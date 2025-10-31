<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Fixtures;

use Tourze\JsonRPCEndpointBundle\Traits\InterruptCallbackTrait;

/**
 * 创建一个使用InterruptCallbackTrait的测试类
 */
class TestClassWithInterruptCallbackTrait
{
    use InterruptCallbackTrait;

    public function executeWithCallback(): mixed
    {
        // 检查属性是否已设置
        try {
            $callback = $this->getInterruptCallback();

            return call_user_func($callback);
        } catch (\Error $e) {
            // 属性未初始化时返回默认值
            return 'default_result';
        }
    }
}
