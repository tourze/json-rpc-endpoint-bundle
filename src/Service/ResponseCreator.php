<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * Class ResponseCreator
 */
class ResponseCreator
{
    public function createEmptyResponse(?JsonRpcRequest $fromRequest = null): JsonRpcResponse
    {
        if (null === $fromRequest) {
            return new JsonRpcResponse();
        }

        $response = new JsonRpcResponse();
        $response->setJsonrpc($fromRequest->getJsonrpc());
        $response->setIsNotification($fromRequest->isNotification());

        if (null !== $fromRequest->getId()) {
            $response->setId($fromRequest->getId());
        }

        return $response;
    }

    public function createResultResponse(mixed $result, ?JsonRpcRequest $fromRequest = null): JsonRpcResponse
    {
        $response = $this->createEmptyResponse($fromRequest);
        $response->setResult($result);

        return $response;
    }

    public function createErrorResponse(\Throwable $exception, ?JsonRpcRequest $fromRequest = null): JsonRpcResponse
    {
        $response = $this->createEmptyResponse($fromRequest);
        $response->setError(
            $exception instanceof JsonRpcExceptionInterface
                ? $exception
                : new JsonRpcInternalErrorException($exception)
        );

        return $response;
    }
}
