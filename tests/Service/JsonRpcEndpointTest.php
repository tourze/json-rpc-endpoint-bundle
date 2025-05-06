<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;
use Tourze\JsonRPC\Core\Event\ResponseSendingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\EventSubscriber\JsonRpcResultListener;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallSerializer;
use Tourze\JsonRPCEndpointBundle\Service\ExceptionHandler;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;

class JsonRpcEndpointTest extends TestCase
{
    private JsonRpcEndpoint $endpoint;
    private JsonRpcCallSerializer|MockObject $serializer;
    private JsonRpcRequestHandler|MockObject $requestHandler;
    private ExceptionHandler|MockObject $exceptionHandler;
    private LoggerInterface|MockObject $logger;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private JsonRpcResultListener|MockObject $resultListener;
    private CacheInterface|MockObject $cache;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(JsonRpcCallSerializer::class);
        $this->requestHandler = $this->createMock(JsonRpcRequestHandler::class);
        $this->exceptionHandler = $this->createMock(ExceptionHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->resultListener = $this->createMock(JsonRpcResultListener::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->endpoint = new JsonRpcEndpoint(
            $this->serializer,
            $this->requestHandler,
            $this->exceptionHandler,
            $this->logger,
            $this->eventDispatcher,
            $this->resultListener,
            $this->cache
        );
    }

    public function testIndex_withValidPayload_returnsResponse(): void
    {
        $payload = '{"jsonrpc":"2.0","method":"test","id":1}';
        $request = $this->createMock(Request::class);
        $jsonRpcCall = $this->createMock(JsonRpcCallRequest::class);
        $jsonRpcRequest = new JsonRpcRequest();
        $jsonRpcResponse = new JsonRpcResponse();
        $jsonRpcCallResponse = new JsonRpcCallResponse();
        $jsonRpcCallResponse->addResponse($jsonRpcResponse);
        $responseString = '{"jsonrpc":"2.0","result":"success","id":1}';

        // RequestStartEvent
        $this->eventDispatcher->expects($this->any())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($request, $payload, $responseString, $jsonRpcCallResponse, $jsonRpcCall) {
                if ($event instanceof RequestStartEvent) {
                    $this->assertEquals($payload, $event->getPayload());
                    $this->assertSame($request, $event->getRequest());
                    return $event;
                } elseif ($event instanceof ResponseSendingEvent) {
                    $this->assertSame($request, $event->getRequest());
                    $this->assertEquals($responseString, $event->getResponseString());
                    $this->assertInstanceOf(JsonRpcCallResponse::class, $event->getJsonRpcCallResponse());
                    $this->assertSame($jsonRpcCall, $event->getJsonRpcCall());
                    return $event;
                }
                return $event;
            });

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($payload)
            ->willReturn($jsonRpcCall);

        $jsonRpcCall->expects($this->atLeast(1))
            ->method('getItemList')
            ->willReturn([$jsonRpcRequest]);

        $jsonRpcCall->expects($this->atLeast(1))
            ->method('isBatch')
            ->willReturn(false);

        $this->requestHandler->expects($this->once())
            ->method('processJsonRpcRequest')
            ->with($jsonRpcRequest)
            ->willReturn($jsonRpcResponse);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->callback(function (JsonRpcCallResponse $response) {
                return $response instanceof JsonRpcCallResponse && count($response->getResponseList()) > 0;
            }))
            ->willReturn($responseString);

        $this->resultListener->expects($this->atLeast(1))
            ->method('getResult')
            ->willReturn([]);

        $this->resultListener->expects($this->once())
            ->method('setResult')
            ->with([]);

        $this->logger->expects($this->atLeast(1))
            ->method('debug');

        $result = $this->endpoint->index($payload, $request);

        $this->assertSame($responseString, $result);
    }

    public function testIndex_withEmptyPayload_returnsEmptyString(): void
    {
        $payload = '';
        $request = $this->createMock(Request::class);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (RequestStartEvent $event) use ($payload, $request) {
                return $event->getPayload() === $payload && $event->getRequest() === $request;
            }))
            ->willReturnArgument(0);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('JsonRPC请求时，传入了无效的字符串', ['request' => $payload]);

        $result = $this->endpoint->index($payload, $request);

        $this->assertSame('', $result);
    }

    public function testIndex_withInvalidJsonPayload_returnsEmptyString(): void
    {
        $payload = '{invalid json}';
        $request = $this->createMock(Request::class);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (RequestStartEvent $event) use ($payload, $request) {
                return $event->getPayload() === $payload && $event->getRequest() === $request;
            }))
            ->willReturnArgument(0);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('JsonRPC请求时，传入了无效的字符串', ['request' => $payload]);

        $result = $this->endpoint->index($payload, $request);

        $this->assertSame('', $result);
    }

    public function testIndex_whenDeserializeThrowsException_returnsErrorResponse(): void
    {
        $payload = '{"jsonrpc":"2.0","method":"test","id":1}';
        $request = $this->createMock(Request::class);
        $exception = new \Exception('Test exception');
        $jsonRpcResponse = new JsonRpcResponse();
        $jsonRpcCallResponse = new JsonRpcCallResponse();
        $jsonRpcCallResponse->addResponse($jsonRpcResponse);
        $responseString = '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":null}';

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($request, $payload, $responseString) {
                if ($event instanceof RequestStartEvent) {
                    $this->assertEquals($payload, $event->getPayload());
                    $this->assertSame($request, $event->getRequest());
                    return $event;
                } elseif ($event instanceof ResponseSendingEvent) {
                    $this->assertSame($request, $event->getRequest());
                    $this->assertEquals($responseString, $event->getResponseString());
                    $this->assertInstanceOf(JsonRpcCallResponse::class, $event->getJsonRpcCallResponse());
                    return $event;
                }
                return $event;
            });

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($payload)
            ->willThrowException($exception);

        $this->exceptionHandler->expects($this->once())
            ->method('getJsonRpcResponseFromException')
            ->with($exception)
            ->willReturn($jsonRpcResponse);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->callback(function (JsonRpcCallResponse $response) {
                return count($response->getResponseList()) === 1;
            }))
            ->willReturn($responseString);

        $this->logger->expects($this->atLeast(1))
            ->method('debug');

        $this->resultListener->expects($this->atLeast(1))
            ->method('getResult')
            ->willReturn([]);

        $this->resultListener->expects($this->once())
            ->method('setResult')
            ->with([]);

        $result = $this->endpoint->index($payload, $request);

        $this->assertSame($responseString, $result);
    }

    public function testReset_resetsStopwatch(): void
    {
        $this->endpoint->reset();

        // There's no way to verify Stopwatch was reset, so we'll just ensure no exception is thrown
        $this->assertTrue(true);
    }

    public function testGetResponseString_withResultAndAdditionalData_mergesData(): void
    {
        $jsonRpcResponse = new JsonRpcResponse();
        $jsonRpcResponse->setResult(['original' => 'data']);

        $jsonRpcCallResponse = new JsonRpcCallResponse();
        $jsonRpcCallResponse->addResponse($jsonRpcResponse);

        $responseString = '{"jsonrpc":"2.0","result":{"original":"data","additional":"data"},"id":null}';

        $this->resultListener->expects($this->once())
            ->method('getResult')
            ->willReturn(['additional' => 'data']);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('补充额外的返回值', [
                'append' => ['additional' => 'data'],
                'result' => ['original' => 'data'],
            ]);

        $this->resultListener->expects($this->once())
            ->method('setResult')
            ->with([]);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($jsonRpcCallResponse)
            ->willReturn($responseString);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'getResponseString');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $jsonRpcCallResponse);

        $this->assertSame($responseString, $result);
        $this->assertSame(
            ['original' => 'data', 'additional' => 'data'],
            $jsonRpcResponse->getResult()
        );
    }

    public function testGetJsonRpcCallResponse_withSingleRequest_processesItem(): void
    {
        $jsonRpcRequest = new JsonRpcRequest();
        $jsonRpcResponse = new JsonRpcResponse();

        $jsonRpcCall = $this->createMock(JsonRpcCallRequest::class);
        $jsonRpcCall->expects($this->atLeast(1))
            ->method('isBatch')
            ->willReturn(false);
        $jsonRpcCall->expects($this->once())
            ->method('getItemList')
            ->willReturn([$jsonRpcRequest]);

        $this->requestHandler->expects($this->once())
            ->method('processJsonRpcRequest')
            ->with($jsonRpcRequest)
            ->willReturn($jsonRpcResponse);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'getJsonRpcCallResponse');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $jsonRpcCall, '{"jsonrpc":"2.0","method":"test","id":1}');

        $this->assertInstanceOf(JsonRpcCallResponse::class, $result);
        $this->assertFalse($result->isBatch());
        $this->assertCount(1, $result->getResponseList());
        $this->assertSame($jsonRpcResponse, $result->getResponseList()[0]);
    }

    public function testGetJsonRpcCallResponse_withBatchRequest_dispatchesEventsAndProcessesItems(): void
    {
        $jsonRpcRequest1 = new JsonRpcRequest();
        $jsonRpcRequest2 = new JsonRpcRequest();
        $jsonRpcResponse1 = new JsonRpcResponse();
        $jsonRpcResponse2 = new JsonRpcResponse();

        $jsonRpcCall = $this->createMock(JsonRpcCallRequest::class);
        $jsonRpcCall->expects($this->atLeast(1))
            ->method('isBatch')
            ->willReturn(true);
        $jsonRpcCall->expects($this->once())
            ->method('getItemList')
            ->willReturn([$jsonRpcRequest1, $jsonRpcRequest2]);

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');

        $this->requestHandler->expects($this->exactly(2))
            ->method('processJsonRpcRequest')
            ->willReturnOnConsecutiveCalls($jsonRpcResponse1, $jsonRpcResponse2);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'getJsonRpcCallResponse');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $jsonRpcCall, '[{"jsonrpc":"2.0","method":"test1","id":1},{"jsonrpc":"2.0","method":"test2","id":2}]');

        $this->assertInstanceOf(JsonRpcCallResponse::class, $result);
        $this->assertTrue($result->isBatch());
        $this->assertCount(2, $result->getResponseList());
        $this->assertSame($jsonRpcResponse1, $result->getResponseList()[0]);
        $this->assertSame($jsonRpcResponse2, $result->getResponseList()[1]);
    }

    public function testProcessItem_withJsonRpcRequest_processesRequest(): void
    {
        $jsonRpcRequest = new JsonRpcRequest();
        $jsonRpcResponse = new JsonRpcResponse();

        $this->requestHandler->expects($this->once())
            ->method('processJsonRpcRequest')
            ->with($jsonRpcRequest)
            ->willReturn($jsonRpcResponse);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'processItem');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $jsonRpcRequest);

        $this->assertSame($jsonRpcResponse, $result);
    }

    public function testProcessItem_withException_returnsErrorResponse(): void
    {
        $exception = new \Exception('Test exception');
        $jsonRpcResponse = new JsonRpcResponse();

        $this->exceptionHandler->expects($this->once())
            ->method('getJsonRpcResponseFromException')
            ->with($exception, null)
            ->willReturn($jsonRpcResponse);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'processItem');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $exception);

        $this->assertSame($jsonRpcResponse, $result);
    }

    public function testProcessItem_whenRequestProcessingThrowsException_returnsErrorResponse(): void
    {
        $jsonRpcRequest = new JsonRpcRequest();
        $exception = new \Exception('Test exception');
        $jsonRpcResponse = new JsonRpcResponse();

        $this->requestHandler->expects($this->once())
            ->method('processJsonRpcRequest')
            ->with($jsonRpcRequest)
            ->willThrowException($exception);

        $this->exceptionHandler->expects($this->once())
            ->method('getJsonRpcResponseFromException')
            ->with($exception, $jsonRpcRequest)
            ->willReturn($jsonRpcResponse);

        // Call protected method through reflection
        $method = new \ReflectionMethod(JsonRpcEndpoint::class, 'processItem');
        $method->setAccessible(true);
        $result = $method->invoke($this->endpoint, $jsonRpcRequest);

        $this->assertSame($jsonRpcResponse, $result);
    }
}
