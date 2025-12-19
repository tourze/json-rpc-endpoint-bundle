<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRPCEndpointBundle::class)]
#[RunTestsInSeparateProcesses]
final class JsonRPCEndpointBundleTest extends AbstractBundleTestCase
{
    protected function onSetUp(): void
    {
        // No additional setup needed
    }
}
