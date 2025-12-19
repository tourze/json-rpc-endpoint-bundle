<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Tests\Param;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPCEndpointBundle\Param\GetServerTimeParam;

/**
 * @internal
 */
#[CoversClass(GetServerTimeParam::class)]
final class GetServerTimeParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new GetServerTimeParam();

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testParamIsReadonly(): void
    {
        $param = new GetServerTimeParam();

        $this->assertInstanceOf(GetServerTimeParam::class, $param);
    }
}
