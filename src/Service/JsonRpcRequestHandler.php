<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Carbon\CarbonImmutable;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Contracts\RequestHandlerInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\MethodExecuteFailureEvent;
use Tourze\JsonRPC\Core\Event\MethodExecuteSuccessEvent;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidParamsException;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCContainerBundle\Service\MethodResolver;

/**
 * JSON-RPC 请求处理器
 */
#[AsAlias(id: RequestHandlerInterface::class)]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'json_rpc_endpoint')]
readonly class JsonRpcRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private MethodResolver $methodResolver,
        private ResponseCreator $responseCreator,
        private EventDispatcherInterface $eventDispatcher,
        private JsonRpcParamsValidator $methodParamsValidator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws JsonRpcInvalidParamsException
     * @throws JsonRpcMethodNotFoundException
     */
    public function processJsonRpcRequest(JsonRpcRequest $request): JsonRpcResponse
    {
        $method = $this->resolveMethod($request);

        $this->validateParamList($request, $method);

        $startTime = CarbonImmutable::now();
        try {
            $result = $method->__invoke($request);
            $endTime = CarbonImmutable::now();

            $event = new MethodExecuteSuccessEvent();
            $event->setStartTime($startTime);
            $event->setEndTime($endTime);
            $event->setResult($result);
            $event->setMethod($method);
            $event->setJsonRpcRequest($request);
            $this->eventDispatcher->dispatch($event);
        } catch (\Throwable $exception) {
            if ($exception instanceof JsonRpcExceptionInterface) {
                $this->logger->warning('JsonRPC执行时发生已知异常', [
                    'exception' => $exception,
                    'request' => $request,
                ]);
            } else {
                $this->logger->error('JsonRPC执行时发生未知异常', [
                    'exception' => $exception,
                    'request' => $request,
                ]);
            }

            $endTime = CarbonImmutable::now();

            $event = new MethodExecuteFailureEvent();
            $event->setStartTime($startTime);
            $event->setEndTime($endTime);
            $event->setException($exception);
            $event->setMethod($method);
            $event->setJsonRpcRequest($request);
            $this->eventDispatcher->dispatch($event);
        }

        return $this->createResponse($event);
    }

    /**
     * @throws JsonRpcMethodNotFoundException
     */
    public function resolveMethod(JsonRpcRequest $request): JsonRpcMethodInterface
    {
        $method = $this->methodResolver->resolve($request->getMethod());

        if (!$method instanceof JsonRpcMethodInterface) {
            throw new JsonRpcMethodNotFoundException($request->getMethod(), ['class' => null]);
        }

        return $method;
    }

    /**
     * @throws JsonRpcInvalidParamsException
     */
    private function validateParamList(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): void
    {
        $violationList = $this->methodParamsValidator->validate($jsonRpcRequest, $method);

        if ([] !== $violationList) {
            // Convert array<int, string> to array<string, mixed> for JsonRpcInvalidParamsException
            $violationMap = [];
            foreach ($violationList as $index => $message) {
                $violationMap['param_' . $index] = $message;
            }
            throw new JsonRpcInvalidParamsException($violationMap);
        }
    }

    protected function createResponse(MethodExecuteSuccessEvent|MethodExecuteFailureEvent $event): JsonRpcResponse
    {
        if ($event instanceof MethodExecuteSuccessEvent) {
            return $this->responseCreator->createResultResponse($event->getResult(), $event->getJsonRpcRequest());
        }

        /* @var $event MethodExecuteFailureEvent */
        return $this->responseCreator->createErrorResponse(
            $event->getException(),
            $event->getJsonRpcRequest()
        );
    }
}
