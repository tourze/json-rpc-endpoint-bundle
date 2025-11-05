<?php

namespace Tourze\JsonRPCEndpointBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodParamsValidatorInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Class JsonRpcParamsValidator
 */
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'procedure')]
readonly class JsonRpcParamsValidator implements JsonRpcMethodParamsValidatorInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): array
    {
        $violationList = [];
        if (!$method instanceof MethodWithValidatedParamsInterface) {
            return $violationList;
        }

        $params = $jsonRpcRequest->getParams();
        $value = $params?->toArray() ?? [];
        $constraints = $method->getParamsConstraint();
        $this->logger->debug('进行JsonRPC请求参数校验', [
            'value' => $value,
            'constraints' => $constraints,
            'method' => $method::class,
        ]);
        $sfViolationList = $this->validator->validate($value, $constraints);

        foreach ($sfViolationList as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $path = $violation->getPropertyPath();
            $message = $violation->getMessage();
            $violationList[] = ('' !== $path) ? sprintf('%s: %s', $path, $message) : $message;
        }

        return $violationList;
    }
}
