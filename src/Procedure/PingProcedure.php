<?php

namespace Tourze\JsonRPCEndpointBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCEndpointBundle\Param\PingProcedureParam;

#[MethodTag(name: '系统服务')]
#[MethodDoc(summary: 'Ping/Pong处理')]
#[MethodExpose(method: 'ping')]
class PingProcedure extends BaseProcedure
{
    /**
     * @phpstan-param PingProcedureParam $param
     */
    public function execute(PingProcedureParam|RpcParamInterface $param): ArrayResult
    {
        return new ArrayResult([
            'pong' => 'pong',
        ]);
    }
}
