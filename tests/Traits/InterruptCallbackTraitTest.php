<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCEndpointBundle\Tests\Fixtures\TestClassWithInterruptCallbackTrait;
use Tourze\JsonRPCEndpointBundle\Traits\InterruptCallbackTrait;

/**
 * @internal
 */
#[CoversClass(InterruptCallbackTrait::class)]
final class InterruptCallbackTraitTest extends TestCase
{
    private TestClassWithInterruptCallbackTrait $testObject;

    protected function setUp(): void
    {
        // 直接创建测试对象实例，因为这是一个trait测试类，不需要容器服务
        $this->testObject = new TestClassWithInterruptCallbackTrait();
    }

    public function testSetInterruptCallbackWithSimpleFunctionSetsCallback(): void
    {
        $callback = function () {
            return 'callback_result';
        };

        $this->testObject->setInterruptCallback($callback);

        $this->assertSame($callback, $this->testObject->getInterruptCallback());
    }

    public function testExecuteWithCallbackWithClosureExecutesCorrectly(): void
    {
        $expectedResult = 'closure_result';
        $callback = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->testObject->setInterruptCallback($callback);
        $result = $this->testObject->executeWithCallback();

        $this->assertSame($expectedResult, $result);
    }

    public function testExecuteWithCallbackWithoutCallbackReturnsDefault(): void
    {
        // 验证初始状态下callback为null
        $this->assertNull($this->testObject->getInterruptCallback());

        $result = $this->testObject->executeWithCallback();

        $this->assertSame('default_result', $result);
    }

    public function testSetInterruptCallbackOverwritesPreviousCallback(): void
    {
        $firstCallback = function () {
            return 'first';
        };

        $secondCallback = function () {
            return 'second';
        };

        $this->testObject->setInterruptCallback($firstCallback);
        $this->testObject->setInterruptCallback($secondCallback);

        $result = $this->testObject->executeWithCallback();
        $this->assertSame('second', $result);
    }

    public function testSetInterruptCallbackToNull(): void
    {
        $callback = function () {
            return 'test_result';
        };

        $this->testObject->setInterruptCallback($callback);
        $this->assertSame($callback, $this->testObject->getInterruptCallback());

        // 设置为null
        $this->testObject->setInterruptCallback(null);
        $this->assertNull($this->testObject->getInterruptCallback());

        // 执行应该返回默认值
        $result = $this->testObject->executeWithCallback();
        $this->assertSame('default_result', $result);
    }
}
