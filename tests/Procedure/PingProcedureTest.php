<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Param\PingProcedureParam;
use Tourze\JsonRPCEndpointBundle\Procedure\PingProcedure;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

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
        $param = new PingProcedureParam();
        $result = $procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->toArray();
        $this->assertArrayHasKey('pong', $resultArray);
        $this->assertSame('pong', $resultArray['pong']);
    }

    public function testInvokeWithRequestReturnsPong(): void
    {
        $procedure = $this->getProcedure();
        $request = new JsonRpcRequest();
        $request->setMethod('ping');
        $request->setId(1);

        $result = $procedure->__invoke($request);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->toArray();
        $this->assertIsArray($resultArray);
        $this->assertArrayHasKey('pong', $resultArray);
        $this->assertSame('pong', $resultArray['pong']);
    }

    public function testExecuteAndInvokeReturnSameResult(): void
    {
        $procedure = $this->getProcedure();
        $request = new JsonRpcRequest();
        $request->setMethod('ping');
        $request->setId(1);
        $param = new PingProcedureParam();

        $executeResult = $procedure->execute($param);
        $invokeResult = $procedure->__invoke($request);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $executeResult);
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $invokeResult);
        $this->assertSame($executeResult->toArray(), $invokeResult->toArray());
        $this->assertSame(['pong' => 'pong'], $executeResult->toArray());
    }

    public function testMultipleCallsReturnConsistentResult(): void
    {
        $procedure = $this->getProcedure();
        $param = new PingProcedureParam();
        $result1 = $procedure->execute($param);
        $result2 = $procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result1);
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result2);
        $this->assertSame($result1->toArray(), $result2->toArray());
        $this->assertSame(['pong' => 'pong'], $result1->toArray());
    }
}
