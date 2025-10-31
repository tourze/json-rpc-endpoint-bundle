<?php

namespace Tourze\JsonRPCEndpointBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

#[MethodTag(name: '系统服务')]
#[MethodDoc(summary: '获取服务器当前时间戳')]
#[MethodExpose(method: 'GetServerTime')]
class GetServerTime implements JsonRpcMethodInterface
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'time' => time(),
        ];
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        return $this->execute();
    }
}
