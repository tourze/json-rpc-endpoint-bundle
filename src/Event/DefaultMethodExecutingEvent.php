<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Event;

use Tourze\JsonRPC\Core\Event\MethodExecutingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * 默认的方法执行事件实现
 */
final class DefaultMethodExecutingEvent extends MethodExecutingEvent
{
    public function __construct(JsonRpcRequest $item, ?JsonRpcResponse $response = null)
    {
        $this->setItem($item);
        $this->setResponse($response);
    }
}
