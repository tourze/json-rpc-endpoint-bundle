<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use Tourze\JsonRPCEndpointBundle\Procedure\PingProcedure;

/**
 * @internal
 */
#[CoversClass(PingProcedure::class)]
#[RunTestsInSeparateProcesses]
final class PingProcedureTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // No additional setup needed
    }

    private function getProcedure(): PingProcedure
    {
        return self::getService(PingProcedure::class);
    }

    public function testExecuteReturnsPong(): void
    {
        $procedure = $this->getProcedure();
        $result = $procedure->execute();

        $this->assertArrayHasKey('pong', $result);
        $this->assertSame('pong', $result['pong']);
    }

    public function testInvokeWithRequestReturnsPong(): void
    {
        $procedure = $this->getProcedure();
        $request = new JsonRpcRequest();
        $request->setMethod('ping');
        $request->setId(1);

        $result = $procedure->__invoke($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pong', $result);
        $this->assertSame('pong', $result['pong']);
    }

    public function testExecuteAndInvokeReturnSameResult(): void
    {
        $procedure = $this->getProcedure();
        $request = new JsonRpcRequest();
        $request->setMethod('ping');
        $request->setId(1);

        $executeResult = $procedure->execute();
        $invokeResult = $procedure->__invoke($request);

        $this->assertSame($executeResult, $invokeResult);
        $this->assertSame(['pong' => 'pong'], $executeResult);
    }

    public function testMultipleCallsReturnConsistentResult(): void
    {
        $procedure = $this->getProcedure();
        $result1 = $procedure->execute();
        $result2 = $procedure->execute();

        $this->assertSame($result1, $result2);
        $this->assertSame(['pong' => 'pong'], $result1);
    }
}
