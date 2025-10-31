<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Service\ResponseCreator;

/**
 * @internal
 */
#[CoversClass(ResponseCreator::class)]
final class ResponseCreatorTest extends TestCase
{
    private ResponseCreator $responseCreator;

    private JsonRpcRequest|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->responseCreator = new ResponseCreator();
        // 使用JsonRpcRequest具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $this->requestMock = $this->createMock(JsonRpcRequest::class);
    }

    public function testCreateEmptyResponseWithNoRequestReturnsEmptyResponse(): void
    {
        $response = $this->responseCreator->createEmptyResponse();

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertNull($response->getId());
        // 初始值可能不是null，不要断言jsonrpc的值
    }

    public function testCreateEmptyResponseWithRequestReturnsResponseWithRequestInfo(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0')
        ;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id')
        ;
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(true)
        ;

        /** @var JsonRpcRequest $request */
        $request = $this->requestMock;
        $response = $this->responseCreator->createEmptyResponse($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertTrue($response->isNotification());
    }

    public function testCreateEmptyResponseWithRequestWithNullIdReturnsResponseWithoutId(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0')
        ;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null)
        ;
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        /** @var JsonRpcRequest $request */
        $request = $this->requestMock;
        $response = $this->responseCreator->createEmptyResponse($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertNull($response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertFalse($response->isNotification());
    }

    public function testCreateResultResponseWithNoRequestReturnsResponseWithResult(): void
    {
        $result = ['data' => 'test'];

        $response = $this->responseCreator->createResultResponse($result);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($result, $response->getResult());
        $this->assertNull($response->getError());
    }

    public function testCreateResultResponseWithRequestReturnsResponseWithRequestInfoAndResult(): void
    {
        $result = ['data' => 'test'];

        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0')
        ;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id')
        ;
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        /** @var JsonRpcRequest $request */
        $request = $this->requestMock;
        $response = $this->responseCreator->createResultResponse($result, $request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($result, $response->getResult());
        $this->assertNull($response->getError());
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
    }

    public function testCreateErrorResponseWithJsonRpcExceptionReturnsResponseWithError(): void
    {
        // 使用具体的JsonRpcException实现类，而不是接口
        $exception = new JsonRpcInternalErrorException(new \Exception('Test exception'));

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($exception, $response->getError());
        $this->assertNull($response->getResult());
    }

    public function testCreateErrorResponseWithNonJsonRpcExceptionReturnsResponseWithInternalError(): void
    {
        $exception = new \Exception('Test exception');

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertInstanceOf(JsonRpcInternalErrorException::class, $response->getError());
        $this->assertNull($response->getResult());
    }

    public function testCreateErrorResponseWithRequestAndExceptionReturnsResponseWithRequestInfoAndError(): void
    {
        // 使用具体的JsonRpcException实现类，而不是接口
        $exception = new JsonRpcInternalErrorException(new \Exception('Test exception'));

        $this->requestMock->expects($this->once())
            ->method('getJsonrpc')
            ->willReturn('2.0')
        ;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('test-id')
        ;
        $this->requestMock->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        /** @var JsonRpcRequest $request */
        $request = $this->requestMock;
        $response = $this->responseCreator->createErrorResponse($exception, $request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($exception, $response->getError());
        $this->assertNull($response->getResult());
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
    }
}
