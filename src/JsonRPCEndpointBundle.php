<?php

namespace Tourze\JsonRPCEndpointBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;

class JsonRPCEndpointBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcRequestHandler::class))->getFileName());
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcCallDenormalizer::class))->getFileName());
    }
}
