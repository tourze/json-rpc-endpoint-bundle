<?php

namespace Tourze\JsonRPCEndpointBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPCContainerBundle\JsonRPCContainerBundle;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;

class JsonRPCEndpointBundle extends Bundle implements BundleDependencyInterface
{
    public function boot(): void
    {
        parent::boot();
        $baseProcedureFile = (new \ReflectionClass(BaseProcedure::class))->getFileName();
        if (false !== $baseProcedureFile) {
            Backtrace::addProdIgnoreFiles($baseProcedureFile);
        }

        $requestHandlerFile = (new \ReflectionClass(JsonRpcRequestHandler::class))->getFileName();
        if (false !== $requestHandlerFile) {
            Backtrace::addProdIgnoreFiles($requestHandlerFile);
        }

        $denormalizerFile = (new \ReflectionClass(JsonRpcCallDenormalizer::class))->getFileName();
        if (false !== $denormalizerFile) {
            Backtrace::addProdIgnoreFiles($denormalizerFile);
        }

        $endpointFile = (new \ReflectionClass(JsonRpcEndpoint::class))->getFileName();
        if (false !== $endpointFile) {
            Backtrace::addProdIgnoreFiles($endpointFile);
        }
    }

    public static function getBundleDependencies(): array
    {
        return [
            JsonRPCContainerBundle::class => ['all' => true],
        ];
    }
}
