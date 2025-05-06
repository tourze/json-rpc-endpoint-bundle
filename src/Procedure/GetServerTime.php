<?php

namespace Tourze\JsonRPCEndpointBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

#[MethodExpose('GetServerTime')]
class GetServerTime implements JsonRpcMethodInterface
{
    public function execute(): array
    {
        return [
            'time' =>  time(),
        ];
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        return $this->execute();
    }
}
