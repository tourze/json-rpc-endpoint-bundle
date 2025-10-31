# JSON-RPC Endpoint Bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/test.yml?branch=master)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master)]
(https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle for handling JSON-RPC 2.0 endpoints, providing complete
JSON-RPC request parsing, processing, and response functionality.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [Example Requests](#example-requests)
- [Dependencies](#dependencies)
- [Contributing](#contributing)
- [License](#license)

## Features

- Complete JSON-RPC 2.0 protocol support
- Batch request processing
- Event-driven architecture
- Request/response serialization and deserialization
- Parameter validation
- Exception handling
- Performance monitoring (using Stopwatch)
- Result appending functionality

## Installation

```bash
composer require tourze/json-rpc-endpoint-bundle
```

## Configuration

The bundle provides default configuration and usually doesn't require
additional configuration. If customization is needed, you can configure it
in `config/packages/json_rpc_endpoint.yaml`.

## Usage

### 1. Enable the Bundle in Symfony Project

```php
// config/bundles.php
return [
    // ...
    Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle::class => ['all' => true],
];
```

### 2. Create JSON-RPC Endpoint Controller

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\JsonRPC\Core\Contracts\EndpointInterface;

class JsonRpcController extends AbstractController
{
    public function __construct(
        private readonly EndpointInterface $jsonRpcEndpoint,
    ) {}

    #[Route('/api/jsonrpc', name: 'api_jsonrpc', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $payload = $request->getContent();
        $response = $this->jsonRpcEndpoint->index($payload, $request);
        
        return new Response($response, 200, ['Content-Type' => 'application/json']);
    }
}
```

### 3. Create JSON-RPC Procedures

```php
<?php

namespace App\Procedure;

use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Attribute\Procedure;
use Tourze\JsonRPC\Core\Attribute\Param;
use Symfony\Component\Validator\Constraints as Assert;

#[Procedure('create_user')]
class CreateUserProcedure extends BaseProcedure
{
    #[Param('name', type: 'string', constraints: [new Assert\NotBlank(), new Assert\Length(min: 2, max: 50)])]
    #[Param('email', type: 'string', constraints: [new Assert\Email()])]
    public function __invoke(string $name, string $email): array
    {
        // User creation logic
        return ['id' => 123, 'name' => $name, 'email' => $email];
    }
}
```

## Advanced Usage

### Event Handling

The bundle provides several events for customization:

```php
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JsonRpcEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeMethodApplyEvent::class => 'onBeforeMethodApply',
            AfterMethodApplyEvent::class => 'onAfterMethodApply',
        ];
    }

    public function onBeforeMethodApply(BeforeMethodApplyEvent $event): void
    {
        // Custom logic before method execution
    }

    public function onAfterMethodApply(AfterMethodApplyEvent $event): void
    {
        // Custom logic after method execution
    }
}
```

### Custom Exception Handling

```php
use Tourze\JsonRPC\Core\Exception\JsonRpcException;

class CustomJsonRpcException extends JsonRpcException
{
    public function __construct(string $message = '', int $code = -32000)
    {
        parent::__construct($message, $code);
    }
}
```

### Batch Request Processing

The bundle automatically handles batch requests:

```json
[
    {"jsonrpc": "2.0", "method": "get_time", "id": 1},
    {"jsonrpc": "2.0", "method": "create_user", "params": {"name": "John", "email": "john@example.com"}, "id": 2}
]
```

## Example Requests

### Single Request

```json
{
    "jsonrpc": "2.0",
    "method": "get_user_info",
    "params": {"user_id": 123},
    "id": 1
}
```

### Batch Request

```json
[
    {
        "jsonrpc": "2.0",
        "method": "get_user_info",
        "params": {"user_id": 123},
        "id": 1
    },
    {
        "jsonrpc": "2.0",
        "method": "get_user_info",
        "params": {"user_id": 456},
        "id": 2
    }
]
```

## Dependencies

This bundle requires the following packages:

- **PHP**: ^8.1
- **Symfony**: ^6.4
- **JSON-RPC Core**: `tourze/json-rpc-core` for core JSON-RPC functionality
- **JSON-RPC Container**: `tourze/json-rpc-container-bundle` for procedure container
- **User Event Bundle**: `tourze/user-event-bundle` for event handling

## Contributing

Contributions are welcome! Please read our contributing guidelines and
submit pull requests to our repository.

## License

This project is licensed under the MIT License.