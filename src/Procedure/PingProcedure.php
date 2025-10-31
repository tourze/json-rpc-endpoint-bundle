<?php

namespace Tourze\JsonRPCEndpointBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodTag(name: '系统服务')]
#[MethodDoc(summary: 'Ping/Pong处理')]
#[MethodExpose(method: 'ping')]
class PingProcedure extends BaseProcedure
{
    public function execute(): array
    {
        return [
            'pong' => 'pong',
        ];
    }
}
