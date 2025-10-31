<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Fixtures;

/**
 * 用于测试 JsonRPC 结果处理的测试类
 */
class TestClassWithJsonRpcResult
{
    private array $jsonRpcResult = [];

    public function getJsonRpcResult(): array
    {
        return $this->jsonRpcResult;
    }

    public function setJsonRpcResult(array $jsonRpcResult): void
    {
        $this->jsonRpcResult = $jsonRpcResult;
    }
}
