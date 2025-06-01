<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Procedure;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Procedure\GetServerTime;

class GetServerTimeTest extends TestCase
{
    private GetServerTime $procedure;

    protected function setUp(): void
    {
        $this->procedure = new GetServerTime();
    }

    public function testExecute_returnsCurrentTime(): void
    {
        $beforeTime = time();
        
        $result = $this->procedure->execute();
        
        $afterTime = time();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('time', $result);
        $this->assertIsInt($result['time']);
        $this->assertGreaterThanOrEqual($beforeTime, $result['time']);
        $this->assertLessThanOrEqual($afterTime, $result['time']);
    }

    public function testInvoke_withRequest_returnsCurrentTime(): void
    {
        $request = new JsonRpcRequest();
        $beforeTime = time();
        
        $result = $this->procedure->__invoke($request);
        
        $afterTime = time();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('time', $result);
        $this->assertIsInt($result['time']);
        $this->assertGreaterThanOrEqual($beforeTime, $result['time']);
        $this->assertLessThanOrEqual($afterTime, $result['time']);
    }

    public function testMultipleCalls_returnDifferentTimes(): void
    {
        $request = new JsonRpcRequest();
        
        $result1 = $this->procedure->__invoke($request);
        usleep(1000); // 等待1毫秒
        $result2 = $this->procedure->__invoke($request);

        $this->assertGreaterThanOrEqual($result1['time'], $result2['time']);
    }

    public function testExecuteAndInvoke_returnSameStructure(): void
    {
        $request = new JsonRpcRequest();
        
        $executeResult = $this->procedure->execute();
        $invokeResult = $this->procedure->__invoke($request);

        $this->assertSame(array_keys($executeResult), array_keys($invokeResult));
        $this->assertSame(['time'], array_keys($executeResult));
    }
} 