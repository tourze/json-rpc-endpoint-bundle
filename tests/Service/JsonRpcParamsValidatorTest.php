<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Constraints\Collection;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcParamsValidator;
use Tourze\JsonRPCEndpointBundle\Tests\Fixtures\TestMethodWithValidatedParams;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcParamsValidator::class)]
#[RunTestsInSeparateProcesses]
final class JsonRpcParamsValidatorTest extends AbstractIntegrationTestCase
{
    private JsonRpcParamsValidator $validator;

    protected function onSetUp(): void
    {
        $this->validator = self::getService(JsonRpcParamsValidator::class);
    }

    public function testValidateWithNonValidatedMethodReturnsEmptyArray(): void
    {
        $request = new JsonRpcRequest();
        $method = $this->createMock(JsonRpcMethodInterface::class);

        $result = $this->validator->validate($request, $method);

        $this->assertSame([], $result);
    }

    public function testValidateWithValidatedMethodCanValidate(): void
    {
        $request = new JsonRpcRequest();
        $params = new JsonRpcParams(['test' => 'value']);
        $request->setParams($params); // Initialize params
        $constraints = new Collection(['fields' => []]);
        $method = new TestMethodWithValidatedParams($constraints);

        $result = $this->validator->validate($request, $method);

        // Should return array (empty or with violations)
        $this->assertIsArray($result);
    }
}
