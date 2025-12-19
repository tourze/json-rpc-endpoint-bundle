<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
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

    protected function setUp(): void
    {
        $this->responseCreator = new ResponseCreator();
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
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setId('test-id');
        $response = $this->responseCreator->createEmptyResponse($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertFalse($response->isNotification());
    }

    public function testCreateEmptyResponseWithRequestWithNullIdReturnsResponseWithoutId(): void
    {
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $response = $this->responseCreator->createEmptyResponse($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertNull($response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertTrue($response->isNotification());
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

        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setId('test-id');
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

        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setId('test-id');
        $response = $this->responseCreator->createErrorResponse($exception, $request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame($exception, $response->getError());
        $this->assertNull($response->getResult());
        $this->assertSame('test-id', $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
    }
}
