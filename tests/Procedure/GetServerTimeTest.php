<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Param\GetServerTimeParam;
use Tourze\JsonRPCEndpointBundle\Procedure\GetServerTime;

/**
 * @internal
 */
#[CoversClass(GetServerTime::class)]
final class GetServerTimeTest extends TestCase
{
    private GetServerTime $procedure;

    protected function setUp(): void
    {
        $this->procedure = new GetServerTime();
    }

    public function testExecuteReturnsCurrentTime(): void
    {
        $beforeTime = time();
        $param = new GetServerTimeParam();

        $result = $this->procedure->execute($param);

        $afterTime = time();
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->toArray();
        $this->assertArrayHasKey('time', $resultArray);
        $this->assertIsInt($resultArray['time']);
        $this->assertGreaterThanOrEqual($beforeTime, $resultArray['time']);
        $this->assertLessThanOrEqual($afterTime, $resultArray['time']);
    }

    public function testInvokeWithRequestReturnsCurrentTime(): void
    {
        $request = new JsonRpcRequest();
        $beforeTime = time();

        $result = $this->procedure->__invoke($request);

        $afterTime = time();
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->toArray();
        $this->assertIsArray($resultArray);
        $this->assertArrayHasKey('time', $resultArray);
        $this->assertIsInt($resultArray['time']);
        $this->assertGreaterThanOrEqual($beforeTime, $resultArray['time']);
        $this->assertLessThanOrEqual($afterTime, $resultArray['time']);
    }

    public function testMultipleCallsReturnDifferentTimes(): void
    {
        $request = new JsonRpcRequest();

        $result1 = $this->procedure->__invoke($request);
        usleep(1000); // 等待1毫秒
        $result2 = $this->procedure->__invoke($request);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result1);
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result2);
        $result1Array = $result1->toArray();
        $result2Array = $result2->toArray();
        $this->assertIsArray($result1Array);
        $this->assertIsArray($result2Array);
        $this->assertGreaterThanOrEqual($result1Array['time'], $result2Array['time']);
    }

    public function testExecuteAndInvokeReturnSameStructure(): void
    {
        $request = new JsonRpcRequest();
        $param = new GetServerTimeParam();

        $executeResult = $this->procedure->execute($param);
        $invokeResult = $this->procedure->__invoke($request);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $executeResult);
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $invokeResult);
        $executeResultArray = $executeResult->toArray();
        $invokeResultArray = $invokeResult->toArray();
        $this->assertIsArray($executeResultArray);
        $this->assertIsArray($invokeResultArray);
        $this->assertSame(array_keys($executeResultArray), array_keys($invokeResultArray));
        $this->assertSame(['time'], array_keys($executeResultArray));
    }
}
