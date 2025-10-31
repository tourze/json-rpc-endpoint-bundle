# JSON-RPC Endpoint Bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/test.yml?branch=master)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master)]
(https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

一个用于 Symfony 的 JSON-RPC 2.0 端点处理包，提供完整的
JSON-RPC 请求解析、处理和响应功能。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [使用方法](#使用方法)
- [高级用法](#高级用法)
- [示例请求](#示例请求)
- [依赖包](#依赖包)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 完整的 JSON-RPC 2.0 协议支持
- 批量请求处理
- 事件驱动架构
- 请求/响应序列化和反序列化
- 参数验证
- 异常处理
- 性能监控（使用 Stopwatch）
- 结果追加功能

## 安装

```bash
composer require tourze/json-rpc-endpoint-bundle
```

## 配置

Bundle 提供了默认配置，通常不需要额外配置。如果需要自定义，可以在 `config/packages/json_rpc_endpoint.yaml` 中进行配置。

## 使用方法

### 1. 在 Symfony 项目中启用 Bundle

```php
// config/bundles.php
return [
    // ...
    Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle::class => ['all' => true],
];
```

### 2. 创建 JSON-RPC 端点控制器

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

### 3. 创建 JSON-RPC 过程（Procedure）

```php
<?php

namespace App\Procedure;

use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Attribute\Procedure;
use Tourze\JsonRPC\Core\Attribute\Param;
use Tourze\JsonRPC\Core\Attribute\Result;

#[Procedure('get_user_info')]
class GetUserInfoProcedure extends BaseProcedure
{
    #[Param('user_id', type: 'int')]
    #[Result(type: 'array')]
    public function __invoke(int $user_id): array
    {
        return [
            'id' => $user_id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
    }
}
```

## 高级功能

### 事件监听

该包提供了多个事件，您可以监听这些事件来扩展功能：

```php
<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;
use Tourze\JsonRPC\Core\Event\ResponseSendingEvent;

class JsonRpcEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RequestStartEvent::class => 'onRequestStart',
            ResponseSendingEvent::class => 'onResponseSending',
        ];
    }

    public function onRequestStart(RequestStartEvent $event): void
    {
        // 在请求开始时执行的逻辑
        $payload = $event->getPayload();
        // 可以修改 payload
        $event->setPayload($modifiedPayload);
    }

    public function onResponseSending(ResponseSendingEvent $event): void
    {
        // 在响应发送前执行的逻辑
        $response = $event->getResponseString();
        // 可以修改响应
        $event->setResponseString($modifiedResponse);
    }
}
```

### 参数验证

包支持自动参数验证：

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
        // 创建用户逻辑
        return ['id' => 123, 'name' => $name, 'email' => $email];
    }
}
```

## 高级用法


### 事件处理

Bundle 提供了多个事件用于自定义：

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
        // 方法执行前的自定义逻辑
    }

    public function onAfterMethodApply(AfterMethodApplyEvent $event): void
    {
        // 方法执行后的自定义逻辑
    }
}
```

### 自定义异常处理

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

### 批量请求处理

Bundle 自动处理批量请求：

```json
[
    {"jsonrpc": "2.0", "method": "get_time", "id": 1},
    {"jsonrpc": "2.0", "method": "create_user", "params": {"name": "John", "email": "john@example.com"}, "id": 2}
]
```

## 示例请求

### 单个请求

```json
{
    "jsonrpc": "2.0",
    "method": "get_user_info",
    "params": {"user_id": 123},
    "id": 1
}
```

### 批量请求

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
        "method": "create_user",
        "params": {"name": "John", "email": "john@example.com"},
        "id": 2
    }
]
```

## 依赖包

此包需要以下依赖包：

- **PHP**: ^8.1
- **Symfony**: ^6.4
- **JSON-RPC Core**: `tourze/json-rpc-core` 提供核心 JSON-RPC 功能
- **JSON-RPC Container**: `tourze/json-rpc-container-bundle` 提供过程容器
- **User Event Bundle**: `tourze/user-event-bundle` 提供事件处理

## 贡献

欢迎贡献！请阅读我们的贡献指南并向我们的仓库提交 Pull Request。

## 许可证

本项目采用 MIT 许可证。
