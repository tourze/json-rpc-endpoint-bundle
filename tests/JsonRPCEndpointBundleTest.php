<?php

namespace Tourze\JsonRPCEndpointBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler;

class JsonRPCEndpointBundleTest extends TestCase
{
    private function getProdIgnoreFiles(): array
    {
        $reflection = new \ReflectionClass(Backtrace::class);
        $property = $reflection->getProperty('prodIgnoreFiles');
        $property->setAccessible(true);
        return $property->getValue();
    }

    private function resetProdIgnoreFiles(): void
    {
        $reflection = new \ReflectionClass(Backtrace::class);
        $property = $reflection->getProperty('prodIgnoreFiles');
        $property->setAccessible(true);
        $property->setValue(null, [
            'var/cache/prod/Container',
            'vendor/symfony/dependency-injection/',
            'var/cache/prod/AopProxy__PM__',
            'vendor/symfony/runtime/Runner/Symfony',
            'bin/console',
            'vendor/symfony/messenger',
            'vendor/symfony/doctrine-bridge/Messenger',
        ]);
    }

    protected function setUp(): void
    {
        $this->resetProdIgnoreFiles();
    }

    public function testBoot_configuresBacktraceIgnoreFiles(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $initialFiles = $this->getProdIgnoreFiles();
        
        $bundle->boot();
        
        $finalFiles = $this->getProdIgnoreFiles();
        $this->assertGreaterThan(count($initialFiles), count($finalFiles));
    }

    public function testBoot_addsBaseProcedureToIgnoreFiles(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $baseProcedureFile = (new \ReflectionClass(BaseProcedure::class))->getFileName();
        
        $bundle->boot();
        
        $ignoreFiles = $this->getProdIgnoreFiles();
        $this->assertContains($baseProcedureFile, $ignoreFiles);
    }

    public function testBoot_addsJsonRpcRequestHandlerToIgnoreFiles(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $requestHandlerFile = (new \ReflectionClass(JsonRpcRequestHandler::class))->getFileName();
        
        $this->resetProdIgnoreFiles();
        $bundle->boot();
        
        $ignoreFiles = $this->getProdIgnoreFiles();
        $this->assertContains($requestHandlerFile, $ignoreFiles);
    }

    public function testBoot_addsJsonRpcCallDenormalizerToIgnoreFiles(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $denormalizerFile = (new \ReflectionClass(JsonRpcCallDenormalizer::class))->getFileName();
        
        $this->resetProdIgnoreFiles();
        $bundle->boot();
        
        $ignoreFiles = $this->getProdIgnoreFiles();
        $this->assertContains($denormalizerFile, $ignoreFiles);
    }

    public function testBoot_addsJsonRpcEndpointToIgnoreFiles(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $endpointFile = (new \ReflectionClass(JsonRpcEndpoint::class))->getFileName();
        
        $this->resetProdIgnoreFiles();
        $bundle->boot();
        
        $ignoreFiles = $this->getProdIgnoreFiles();
        $this->assertContains($endpointFile, $ignoreFiles);
    }

    public function testGetBundleDependencies_returnsCorrectDependencies(): void
    {
        $dependencies = JsonRPCEndpointBundle::getBundleDependencies();
        
        $this->assertArrayHasKey(\Tourze\JsonRPCContainerBundle\JsonRPCContainerBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[\Tourze\JsonRPCContainerBundle\JsonRPCContainerBundle::class]);
    }

    public function testBoot_canBeCalledMultipleTimes(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        
        $this->resetProdIgnoreFiles();
        $bundle->boot();
        $firstCallFiles = $this->getProdIgnoreFiles();
        
        $bundle->boot();
        $secondCallFiles = $this->getProdIgnoreFiles();
        
        // 多次调用不应该重复添加文件
        $this->assertEquals($firstCallFiles, $secondCallFiles);
    }

    public function testBundle_implementsBundleDependencyInterface(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $this->assertInstanceOf(\Tourze\BundleDependency\BundleDependencyInterface::class, $bundle);
    }

    public function testBundle_extendsSymfonyBundle(): void
    {
        $bundle = new JsonRPCEndpointBundle();
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\Bundle::class, $bundle);
    }
} 