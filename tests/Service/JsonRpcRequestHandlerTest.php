<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcRequestHandler::class)]
#[RunTestsInSeparateProcesses]
final class JsonRpcRequestHandlerTest extends AbstractIntegrationTestCase
{
    private JsonRpcRequestHandler $requestHandler;

    protected function onSetUp(): void
    {
        $this->requestHandler = self::getService(JsonRpcRequestHandler::class);
    }

    public function testProcessJsonRpcRequestWithValidRequestReturnsResponse(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('GetServerTime');
        $request->setId(1);
        $request->setParams(new JsonRpcParams([])); // Initialize params

        $result = $this->requestHandler->processJsonRpcRequest($request);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->getId());
        $this->assertNull($result->getError());
        $this->assertNotNull($result->getResult());
    }

    public function testProcessJsonRpcRequestWithInvalidMethodThrowsException(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('invalidMethod');
        $request->setId(1);
        $request->setParams(new JsonRpcParams([])); // Initialize params

        $this->expectException(JsonRpcMethodNotFoundException::class);
        $this->requestHandler->processJsonRpcRequest($request);
    }

    public function testResolveMethod(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('GetServerTime');

        $method = $this->requestHandler->resolveMethod($request);

        $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);
    }

    public function testResolveMethodWithInvalidMethodThrowsException(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('invalidMethod');

        $this->expectException(JsonRpcMethodNotFoundException::class);
        $this->requestHandler->resolveMethod($request);
    }
}
