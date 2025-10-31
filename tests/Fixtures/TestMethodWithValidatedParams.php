<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Fixtures;

use Symfony\Component\Validator\Constraints\Collection;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 创建一个实现MethodWithValidatedParamsInterface的具体类，用于测试
 */
class TestMethodWithValidatedParams implements MethodWithValidatedParamsInterface, JsonRpcMethodInterface
{
    private Collection $constraints;

    public function __construct(Collection $constraints)
    {
        $this->constraints = $constraints;
    }

    public function getParamsConstraint(): Collection
    {
        return $this->constraints;
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        return null;
    }

    // 实现所有必要的接口方法
    public function execute(): array
    {
        return [];
    }
}
