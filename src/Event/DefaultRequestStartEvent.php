<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;

/**
 * 默认的请求开始事件实现
 */
final class DefaultRequestStartEvent extends RequestStartEvent
{
    public function __construct(?Request $request = null, string $payload = '')
    {
        $this->setRequest($request);
        $this->setPayload($payload);
    }
}
