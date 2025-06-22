<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
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

class InterruptCallbackTraitTest extends TestCase
{
    private TestClassWithInterruptCallbackTrait $testObject;

    protected function setUp(): void
    {
        $this->testObject = new TestClassWithInterruptCallbackTrait();
    }

    public function testSetInterruptCallback_withSimpleFunction_setsCallback(): void
    {
        $callback = function() {
            return 'callback_result';
        };

        $this->testObject->setInterruptCallback($callback);

        $this->assertSame($callback, $this->testObject->getInterruptCallback());
    }

    public function testExecuteWithCallback_withClosure_executesCorrectly(): void
    {
        $expectedResult = 'closure_result';
        $callback = function() use ($expectedResult) {
            return $expectedResult;
        };

        $this->testObject->setInterruptCallback($callback);
        $result = $this->testObject->executeWithCallback();

        $this->assertSame($expectedResult, $result);
    }

    public function testExecuteWithCallback_withoutCallback_returnsDefault(): void
    {
        $result = $this->testObject->executeWithCallback();

        $this->assertSame('default_result', $result);
    }

    public function testSetInterruptCallback_overwritesPreviousCallback(): void
    {
        $firstCallback = function() {
            return 'first';
        };
        
        $secondCallback = function() {
            return 'second';
        };

        $this->testObject->setInterruptCallback($firstCallback);
        $this->testObject->setInterruptCallback($secondCallback);
        
        $result = $this->testObject->executeWithCallback();
        $this->assertSame('second', $result);
    }
} 