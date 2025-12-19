<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Param;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class PingProcedureParam implements RpcParamInterface
{
    public function __construct()
    {
    }
}
