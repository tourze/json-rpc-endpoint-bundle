<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\JsonRPCEndpointBundle\DependencyInjection\JsonRPCEndpointExtension;

class JsonRPCEndpointExtensionTest extends TestCase
{
    private JsonRPCEndpointExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new JsonRPCEndpointExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad_withEmptyConfig_loadsServicesConfiguration(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\EventSubscriber\JsonRpcResultListener'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\JsonRpcRequestHandler'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\ExceptionHandler'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\ResponseCreator'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\JsonRpcParamsValidator'));
    }

    public function testLoad_withMultipleConfigs_mergesConfigurations(): void
    {
        $configs = [[], []];

        $this->extension->load($configs, $this->container);

        // 验证服务仍然正确加载
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint'));
    }

    public function testLoad_configuresServiceProperties(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);

        // 验证服务是否配置了autowire和autoconfigure
        $definition = $this->container->getDefinition('Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint');
        $this->assertTrue($definition->isAutowired());
        $this->assertTrue($definition->isAutoconfigured());
    }

    public function testLoad_loadsProcedureServices(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Procedure\GetServerTime'));
    }

    public function testLoad_loadsSerializationServices(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallDenormalizer'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallResponseNormalizer'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallSerializer'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcRequestDenormalizer'));
        $this->assertTrue($this->container->hasDefinition('Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcResponseNormalizer'));
    }
} 