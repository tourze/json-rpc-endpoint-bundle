<?php

namespace Tourze\JsonRPCEndpointBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class JsonRPCEndpointExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
