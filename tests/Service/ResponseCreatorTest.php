<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Service\ResponseCreator;

class ResponseCreatorTest extends TestCase
{
    private ResponseCreator $responseCreator;
    private JsonRpcRequest|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->responseCreator = new ResponseCreator();
        $this->requestMock = $this->createMock(JsonRpcRequest::class);
    }

    public function testCreateEmptyResponse_withNoRequest_returnsEmptyResponse(): void
    {
        $response = $this->responseCreator->createEmptyResponse();

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertNull($response->getId());
        // 初始值可能不是null，不要断言jsonrpc的值
    }

    public function testCreateEmptyResponse_withRequest_returnsResponseWithRequestInfo(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id');
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(true);

        $response = $this->responseCreator->createEmptyResponse($this->requestMock);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertTrue($response->isNotification());
    }

    public function testCreateEmptyResponse_withRequestWithNullId_returnsResponseWithoutId(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $response = $this->responseCreator->createEmptyResponse($this->requestMock);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertNull($response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertFalse($response->isNotification());
    }

    public function testCreateResultResponse_withNoRequest_returnsResponseWithResult(): void
    {
        $result = ['data' => 'test'];

        $response = $this->responseCreator->createResultResponse($result);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($result, $response->getResult());
        $this->assertNull($response->getError());
    }

    public function testCreateResultResponse_withRequest_returnsResponseWithRequestInfoAndResult(): void
    {
        $result = ['data' => 'test'];

        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id');
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $response = $this->responseCreator->createResultResponse($result, $this->requestMock);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($result, $response->getResult());
        $this->assertNull($response->getError());
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
    }

    public function testCreateErrorResponse_withJsonRpcException_returnsResponseWithError(): void
    {
        // 使用具体的JsonRpcException实现类，而不是接口
        $exception = new JsonRpcInternalErrorException(new \Exception('Test exception'));

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($exception, $response->getError());
        $this->assertNull($response->getResult());
    }

    public function testCreateErrorResponse_withNonJsonRpcException_returnsResponseWithInternalError(): void
    {
        $exception = new \Exception('Test exception');

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertInstanceOf(JsonRpcInternalErrorException::class, $response->getError());
        $this->assertNull($response->getResult());
    }

    public function testCreateErrorResponse_withRequestAndException_returnsResponseWithRequestInfoAndError(): void
    {
        // 使用具体的JsonRpcException实现类，而不是接口
        $exception = new JsonRpcInternalErrorException(new \Exception('Test exception'));

        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id');
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $response = $this->responseCreator->createErrorResponse($exception, $this->requestMock);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($exception, $response->getError());
        $this->assertNull($response->getResult());
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
    }
}
