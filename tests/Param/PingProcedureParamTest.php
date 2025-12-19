<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Tests\Param;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPCEndpointBundle\Param\PingProcedureParam;

/**
 * @internal
 */
#[CoversClass(PingProcedureParam::class)]
final class PingProcedureParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new PingProcedureParam();

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testParamIsReadonly(): void
    {
        $param = new PingProcedureParam();

        $this->assertInstanceOf(PingProcedureParam::class, $param);
    }
}
