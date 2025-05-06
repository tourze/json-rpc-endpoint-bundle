<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Event\OnExceptionEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Service\ExceptionHandler;
use Tourze\JsonRPCEndpointBundle\Service\ResponseCreator;

class ExceptionHandlerTest extends TestCase
{
    private ExceptionHandler $exceptionHandler;
    private ResponseCreator|MockObject $responseCreator;
    private EventDispatcherInterface|MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->responseCreator = $this->createMock(ResponseCreator::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->exceptionHandler = new ExceptionHandler(
            $this->responseCreator,
            $this->eventDispatcher
        );
    }

    public function testGetJsonRpcResponseFromException_withNoRequest_dispatchesEventAndCreatesResponse(): void
    {
        $exception = new \Exception('Test exception');
        $response = new JsonRpcResponse();

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (OnExceptionEvent $event) use ($exception) {
                return $event->getException() === $exception && $event->getFromJsonRpcRequest() === null;
            }))
            ->willReturnArgument(0);

        $this->responseCreator->expects($this->once())
            ->method('createErrorResponse')
            ->with($exception, null)
            ->willReturn($response);

        $result = $this->exceptionHandler->getJsonRpcResponseFromException($exception);

        $this->assertSame($response, $result);
    }

    public function testGetJsonRpcResponseFromException_withRequest_dispatchesEventAndCreatesResponse(): void
    {
        $exception = new \Exception('Test exception');
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (OnExceptionEvent $event) use ($exception, $request) {
                return $event->getException() === $exception && $event->getFromJsonRpcRequest() === $request;
            }))
            ->willReturnArgument(0);

        $this->responseCreator->expects($this->once())
            ->method('createErrorResponse')
            ->with($exception, $request)
            ->willReturn($response);

        $result = $this->exceptionHandler->getJsonRpcResponseFromException($exception, $request);

        $this->assertSame($response, $result);
    }

    public function testGetJsonRpcResponseFromException_whenEventModifiesException_usesModifiedException(): void
    {
        $originalException = new \Exception('Original exception');
        $modifiedException = new \RuntimeException('Modified exception');
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (OnExceptionEvent $event) {
                return $event instanceof OnExceptionEvent;
            }))
            ->willReturnCallback(function (OnExceptionEvent $event) use ($modifiedException) {
                $event->setException($modifiedException);
                return $event;
            });

        $this->responseCreator->expects($this->once())
            ->method('createErrorResponse')
            ->with($modifiedException, $request)
            ->willReturn($response);

        $result = $this->exceptionHandler->getJsonRpcResponseFromException($originalException, $request);

        $this->assertSame($response, $result);
    }
}
