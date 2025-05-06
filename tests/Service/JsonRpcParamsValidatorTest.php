<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcParamsValidator;

/**
 * 创建一个实现MethodWithValidatedParamsInterface的具体类，用于测试
 */
class TestMethodWithValidatedParams implements MethodWithValidatedParamsInterface, JsonRpcMethodInterface
{
    private Collection $constraints;

    public function __construct(Collection $constraints)
    {
        $this->constraints = $constraints;
    }

    public function getParamsConstraint(): Collection
    {
        return $this->constraints;
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        return null;
    }

    // 实现所有必要的接口方法
    public function execute(): array
    {
        return [];
    }
}

class JsonRpcParamsValidatorTest extends TestCase
{
    private JsonRpcParamsValidator $validator;
    private ValidatorInterface|MockObject $symfonyValidator;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->symfonyValidator = $this->createMock(ValidatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validator = new JsonRpcParamsValidator(
            $this->symfonyValidator,
            $this->logger
        );
    }

    public function testValidate_withNonValidatedMethod_returnsEmptyArray(): void
    {
        $request = new JsonRpcRequest();
        $method = $this->createMock(JsonRpcMethodInterface::class);

        $this->symfonyValidator->expects($this->never())
            ->method('validate');

        $result = $this->validator->validate($request, $method);

        $this->assertSame([], $result);
    }

    public function testValidate_withValidParams_returnsEmptyArray(): void
    {
        $params = ['param1' => 'value1', 'param2' => 'value2'];
        $constraints = $this->createMock(Collection::class);

        $request = new JsonRpcRequest();
        $jsonRpcParams = $this->getMockBuilder(JsonRpcParams::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonRpcParams->expects($this->once())
            ->method('toArray')
            ->willReturn($params);
        $request->setParams($jsonRpcParams);

        $method = new TestMethodWithValidatedParams($constraints);

        $violationList = new ConstraintViolationList();

        $this->symfonyValidator->expects($this->once())
            ->method('validate')
            ->with($params, $constraints)
            ->willReturn($violationList);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('进行JsonRPC请求参数校验', [
                'value' => $params,
                'constraints' => $constraints,
            ]);

        $result = $this->validator->validate($request, $method);

        $this->assertSame([], $result);
    }

    public function testValidate_withInvalidParams_returnsViolations(): void
    {
        $params = ['param1' => 'value1', 'param2' => 'value2'];
        $constraints = $this->createMock(Collection::class);

        $request = new JsonRpcRequest();
        $jsonRpcParams = $this->getMockBuilder(JsonRpcParams::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonRpcParams->expects($this->once())
            ->method('toArray')
            ->willReturn($params);
        $request->setParams($jsonRpcParams);

        $method = new TestMethodWithValidatedParams($constraints);

        $violation1 = $this->createMock(ConstraintViolation::class);
        $violation1->expects($this->once())
            ->method('getPropertyPath')
            ->willReturn('param1');
        $violation1->expects($this->once())
            ->method('getMessage')
            ->willReturn('Error message 1');
        $violation1->expects($this->once())
            ->method('getCode')
            ->willReturn('001');

        $violation2 = $this->createMock(ConstraintViolation::class);
        $violation2->expects($this->once())
            ->method('getPropertyPath')
            ->willReturn('param2');
        $violation2->expects($this->once())
            ->method('getMessage')
            ->willReturn('Error message 2');
        $violation2->expects($this->once())
            ->method('getCode')
            ->willReturn('002');

        $violationList = new ConstraintViolationList([$violation1, $violation2]);

        $this->symfonyValidator->expects($this->once())
            ->method('validate')
            ->with($params, $constraints)
            ->willReturn($violationList);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('进行JsonRPC请求参数校验', [
                'value' => $params,
                'constraints' => $constraints,
            ]);

        $result = $this->validator->validate($request, $method);

        $expectedViolations = [
            [
                'path' => 'param1',
                'message' => 'Error message 1',
                'code' => '001',
            ],
            [
                'path' => 'param2',
                'message' => 'Error message 2',
                'code' => '002',
            ],
        ];

        $this->assertSame($expectedViolations, $result);
    }
}
