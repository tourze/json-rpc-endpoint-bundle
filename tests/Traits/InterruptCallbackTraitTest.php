<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCEndpointBundle\Tests\Fixtures\TestClassWithInterruptCallbackTrait;

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