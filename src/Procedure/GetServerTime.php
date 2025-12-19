<?php

namespace Tourze\JsonRPCEndpointBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Param\GetServerTimeParam;

#[MethodTag(name: '系统服务')]
#[MethodDoc(summary: '获取服务器当前时间戳')]
#[MethodExpose(method: 'GetServerTime')]
class GetServerTime implements JsonRpcMethodInterface
{
    /**
     * @phpstan-param GetServerTimeParam $param
     */
    public function execute(GetServerTimeParam|RpcParamInterface $param): ArrayResult
    {
        return new ArrayResult([
            'time' => time(),
        ]);
    }

    public function __invoke(JsonRpcRequest $request): RpcResultInterface
    {
        return $this->execute(new GetServerTimeParam());
    }
}
