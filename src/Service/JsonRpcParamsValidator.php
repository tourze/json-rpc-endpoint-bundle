<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodParamsValidatorInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Class JsonRpcParamsValidator
 */
#[WithMonologChannel(channel: 'procedure')]
class JsonRpcParamsValidator implements JsonRpcMethodParamsValidatorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): array
    {
        $violationList = [];
        if (!$method instanceof MethodWithValidatedParamsInterface) {
            return $violationList;
        }

        $value = $jsonRpcRequest->getParams()->toArray();
        $constraints = $method->getParamsConstraint();
        $this->logger->debug('进行JsonRPC请求参数校验', [
            'value' => $value,
            'constraints' => $constraints,
            'method' => $method::class,
        ]);
        $sfViolationList = $this->validator->validate($value, $constraints);

        foreach ($sfViolationList as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $violationList[] = [
                'path' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'code' => $violation->getCode(),
            ];
        }

        return $violationList;
    }
}
