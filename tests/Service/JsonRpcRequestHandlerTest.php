<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\MethodExecuteFailureEvent;
use Tourze\JsonRPC\Core\Event\MethodExecuteSuccessEvent;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidParamsException;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCContainerBundle\Service\MethodResolver;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcParamsValidator;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;
use Tourze\JsonRPCEndpointBundle\Service\ResponseCreator;

class JsonRpcRequestHandlerTest extends TestCase
{
    private JsonRpcRequestHandler $requestHandler;
    private MethodResolver|MockObject $methodResolver;
    private ResponseCreator|MockObject $responseCreator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private JsonRpcParamsValidator|MockObject $paramsValidator;

    protected function setUp(): void
    {
        $this->methodResolver = $this->createMock(MethodResolver::class);
        $this->responseCreator = $this->createMock(ResponseCreator::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->paramsValidator = $this->createMock(JsonRpcParamsValidator::class);

        $this->requestHandler = new JsonRpcRequestHandler(
            $this->methodResolver,
            $this->responseCreator,
            $this->eventDispatcher,
            $this->paramsValidator
        );
    }

    public function testProcessJsonRpcRequest_withValidRequest_returnsSuccessResponse(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');

        $method = $this->createMock(JsonRpcMethodInterface::class);
        $result = ['data' => 'test result'];
        $response = new JsonRpcResponse();

        $this->methodResolver->expects($this->once())
            ->method('resolve')
            ->with('test.method')
            ->willReturn($method);

        $this->paramsValidator->expects($this->once())
            ->method('validate')
            ->with($request, $method)
            ->willReturn([]);

        $method->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($result);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($method, $request, $result) {
                return $event instanceof MethodExecuteSuccessEvent
                    && $event->getMethod() === $method
                    && $event->getJsonRpcRequest() === $request
                    && $event->getResult() === $result;
            }));

        $this->responseCreator->expects($this->once())
            ->method('createResultResponse')
            ->with($result, $request)
            ->willReturn($response);

        $returnedResponse = $this->requestHandler->processJsonRpcRequest($request);

        $this->assertSame($response, $returnedResponse);
    }

    public function testProcessJsonRpcRequest_whenMethodThrowsException_returnsErrorResponse(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');

        $method = $this->createMock(JsonRpcMethodInterface::class);
        $exception = new \RuntimeException('Test exception');
        $response = new JsonRpcResponse();

        $this->methodResolver->expects($this->once())
            ->method('resolve')
            ->with('test.method')
            ->willReturn($method);

        $this->paramsValidator->expects($this->once())
            ->method('validate')
            ->with($request, $method)
            ->willReturn([]);

        $method->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willThrowException($exception);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($method, $request, $exception) {
                return $event instanceof MethodExecuteFailureEvent
                    && $event->getMethod() === $method
                    && $event->getJsonRpcRequest() === $request
                    && $event->getException() === $exception;
            }));

        $this->responseCreator->expects($this->once())
            ->method('createErrorResponse')
            ->with($exception, $request)
            ->willReturn($response);

        $returnedResponse = $this->requestHandler->processJsonRpcRequest($request);

        $this->assertSame($response, $returnedResponse);
    }

    public function testResolveMethod_withValidMethodName_returnsMethod(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');

        $method = $this->createMock(JsonRpcMethodInterface::class);

        $this->methodResolver->expects($this->once())
            ->method('resolve')
            ->with('test.method')
            ->willReturn($method);

        $returnedMethod = $this->requestHandler->resolveMethod($request);

        $this->assertSame($method, $returnedMethod);
    }

    public function testResolveMethod_withInvalidMethodName_throwsMethodNotFoundException(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('invalid.method');

        $this->methodResolver->expects($this->once())
            ->method('resolve')
            ->with('invalid.method')
            ->willReturn(null);

        $this->expectException(JsonRpcMethodNotFoundException::class);
        $this->requestHandler->resolveMethod($request);
    }

    public function testResolveMethod_withNonInterfaceMethod_throwsMethodNotFoundException(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');

        // 创建一个不是JsonRpcMethodInterface实例的对象，但需要让methodResolver的返回值能通过类型检查
        $nonInterfaceMethod = null;

        $this->methodResolver->expects($this->once())
            ->method('resolve')
            ->with('test.method')
            ->willReturn($nonInterfaceMethod);

        $this->expectException(JsonRpcMethodNotFoundException::class);
        $this->requestHandler->resolveMethod($request);
    }

    public function testValidateParamList_withInvalidParams_throwsInvalidParamsException(): void
    {
        $request = new JsonRpcRequest();
        $method = $this->createMock(JsonRpcMethodInterface::class);

        $violations = [
            ['path' => 'param1', 'message' => 'Error message', 'code' => '001']
        ];

        $this->paramsValidator->expects($this->once())
            ->method('validate')
            ->with($request, $method)
            ->willReturn($violations);

        $this->expectException(JsonRpcInvalidParamsException::class);

        // Call protected method through reflection
        $validateMethod = new \ReflectionMethod(JsonRpcRequestHandler::class, 'validateParamList');
        $validateMethod->setAccessible(true);
        $validateMethod->invoke($this->requestHandler, $request, $method);
    }

    public function testCreateResponse_withSuccessEvent_returnsResultResponse(): void
    {
        $result = ['data' => 'test'];
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        $event = new MethodExecuteSuccessEvent();
        $event->setResult($result);
        $event->setJsonRpcRequest($request);

        $this->responseCreator->expects($this->once())
            ->method('createResultResponse')
            ->with($result, $request)
            ->willReturn($response);

        // Call protected method through reflection
        $createResponseMethod = new \ReflectionMethod(JsonRpcRequestHandler::class, 'createResponse');
        $createResponseMethod->setAccessible(true);
        $returnedResponse = $createResponseMethod->invoke($this->requestHandler, $event);

        $this->assertSame($response, $returnedResponse);
    }

    public function testCreateResponse_withFailureEvent_returnsErrorResponse(): void
    {
        $exception = new \RuntimeException('Test exception');
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        $event = new MethodExecuteFailureEvent();
        $event->setException($exception);
        $event->setJsonRpcRequest($request);

        $this->responseCreator->expects($this->once())
            ->method('createErrorResponse')
            ->with($exception, $request)
            ->willReturn($response);

        // Call protected method through reflection
        $createResponseMethod = new \ReflectionMethod(JsonRpcRequestHandler::class, 'createResponse');
        $createResponseMethod->setAccessible(true);
        $returnedResponse = $createResponseMethod->invoke($this->requestHandler, $event);

        $this->assertSame($response, $returnedResponse);
    }
}
