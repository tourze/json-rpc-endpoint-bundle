<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle;

/**
 * @internal
 *
 * @phpstan-ignore-next-line Bundle 测试不需要数据库，使用 TestCase 即可
 */
#[CoversClass(JsonRPCEndpointBundle::class)]
#[RunTestsInSeparateProcesses]
final class JsonRPCEndpointBundleTest extends TestCase
{
    public function testBundleHasCorrectPath(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $this->assertStringContainsString('json-rpc-endpoint-bundle', $bundle->getPath());
    }
}
