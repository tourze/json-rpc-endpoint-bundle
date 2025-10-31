<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Event\OnExceptionEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * 异常处理器
 */
readonly class ExceptionHandler
{
    public function __construct(
        private ResponseCreator $responseCreator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getJsonRpcResponseFromException(
        \Throwable $exception,
        ?JsonRpcRequest $fromRequest = null,
    ): JsonRpcResponse {
        $event = new OnExceptionEvent();
        $event->setException($exception);
        $event->setFromJsonRpcRequest($fromRequest);
        $this->eventDispatcher->dispatch($event);

        return $this->responseCreator->createErrorResponse($event->getException(), $fromRequest);
    }
}
