<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\JsonRPC\Core\Contracts\EndpointInterface;
use Tourze\JsonRPC\Core\Event\BatchSubRequestProcessedEvent;
use Tourze\JsonRPC\Core\Event\OnBatchSubRequestProcessingEvent;
use Tourze\JsonRPC\Core\Event\ResponseSendingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallSerializer;
use Tourze\JsonRPCEndpointBundle\Event\DefaultMethodExecutingEvent;
use Tourze\JsonRPCEndpointBundle\Event\DefaultRequestStartEvent;

#[AsAlias(id: EndpointInterface::class)]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'json_rpc_endpoint')]
class JsonRpcEndpoint implements ResetInterface, EndpointInterface
{
    private Stopwatch $stopwatch;

    public function __construct(
        private readonly JsonRpcCallSerializer $jsonRpcCallSerializer,
        private readonly JsonRpcRequestHandler $jsonRpcRequestHandler,
        private readonly ExceptionHandler $exceptionHandler,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->stopwatch = new Stopwatch(true);
    }

    public function index(string $payload, ?Request $request = null): string
    {
        $event = new DefaultRequestStartEvent($request, $payload);
        $this->eventDispatcher->dispatch($event);
        $payload = $event->getPayload();

        if ('' === $payload || !json_validate($payload)) {
            $this->logger->error('JsonRPC请求时，传入了无效的字符串', [
                'request' => $payload,
            ]);

            return '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}';
        }

        $this->logger->debug('JsonRPC接收到请求', [
            'request' => $payload,
        ]);
        $jsonRpcCall = null;
        try {
            $e = $this->stopwatch->start($payload, '执行JsonRPC请求');
            $jsonRpcCall = $this->jsonRpcCallSerializer->deserialize($payload);
            $jsonRpcCallResponse = $this->getJsonRpcCallResponse($jsonRpcCall, $payload);
            $e->stop();
            $resp = $this->getResponseString($jsonRpcCallResponse, $jsonRpcCall);
            $this->logger->debug('执行JsonRPC请求结束', [
                'duration' => $e->getDuration(),
                'memory' => $e->getMemory(),
            ]);
        } catch (\Throwable $exception) {
            // Try to create a valid json-rpc error
            $jsonRpcCallResponse = new JsonRpcCallResponse();
            $jsonRpcCallResponse->addResponse(
                $this->exceptionHandler->getJsonRpcResponseFromException($exception)
            );

            $resp = $this->getResponseString($jsonRpcCallResponse, $jsonRpcCall);
        } finally {
            $this->stopwatch->reset();
        }

        $event = new ResponseSendingEvent();
        $event->setRequest($request);
        $event->setResponseString($resp);
        $event->setJsonRpcCallResponse($jsonRpcCallResponse);
        $event->setJsonRpcCall($jsonRpcCall);
        $this->eventDispatcher->dispatch($event);

        return $event->getResponseString();
    }

    public function reset(): void
    {
        $this->stopwatch->reset();
    }

    protected function getResponseString(
        JsonRpcCallResponse $jsonRpcCallResponse,
        ?JsonRpcCallRequest $jsonRpcCall = null,
    ): string {
        return $this->jsonRpcCallSerializer->serialize($jsonRpcCallResponse);
    }

    protected function getJsonRpcCallResponse(JsonRpcCallRequest $jsonRpcCall, string $request): JsonRpcCallResponse
    {
        $jsonRpcCallResponse = new JsonRpcCallResponse($jsonRpcCall->isBatch());

        foreach ($jsonRpcCall->getItemList() as $itemPosition => $item) {
            if ($jsonRpcCall->isBatch()) {
                $event = new OnBatchSubRequestProcessingEvent();
                $event->setItemPosition($itemPosition);
                $this->eventDispatcher->dispatch($event);
            }

            $jsonRpcCallResponse->addResponse($this->processItem($item));
            if ($jsonRpcCall->isBatch()) {
                $event = new BatchSubRequestProcessedEvent();
                $event->setItemPosition($itemPosition);
                $this->eventDispatcher->dispatch($event);
            }
        }

        return $jsonRpcCallResponse;
    }

    private function processItem(JsonRpcRequest|\Exception $item): JsonRpcResponse
    {
        try {
            if ($item instanceof \Exception) {
                // Exception will be caught just below and converted to response
                throw $item;
            }

            $event = new DefaultMethodExecutingEvent($item);
            $this->eventDispatcher->dispatch($event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            return $this->jsonRpcRequestHandler->processJsonRpcRequest($item);
        } catch (\Throwable $exception) {
            return $this->exceptionHandler->getJsonRpcResponseFromException(
                $exception,
                $item instanceof JsonRpcRequest ? $item : null
            );
        }
    }
}
