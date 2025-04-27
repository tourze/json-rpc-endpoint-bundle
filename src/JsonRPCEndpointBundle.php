<?php

namespace Tourze\JsonRPCEndpointBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;

class JsonRPCEndpointBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(BaseProcedure::class))->getFileName());
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcRequestHandler::class))->getFileName());
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcCallDenormalizer::class))->getFileName());
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcEndpoint::class))->getFileName());
    }
}
