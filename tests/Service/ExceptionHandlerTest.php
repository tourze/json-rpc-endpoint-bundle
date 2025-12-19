<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Service\ExceptionHandler;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ExceptionHandler::class)]
#[RunTestsInSeparateProcesses]
final class ExceptionHandlerTest extends AbstractIntegrationTestCase
{
    private ExceptionHandler $exceptionHandler;

    protected function onSetUp(): void
    {
        $this->exceptionHandler = self::getService(ExceptionHandler::class);
    }

    public function testGetJsonRpcResponseFromExceptionWithNoRequestCreatesResponse(): void
    {
        $exception = new \Exception('Test exception');

        $response = $this->exceptionHandler->getJsonRpcResponseFromException($exception);

        $this->assertNotNull($response);
        $this->assertNotNull($response->getError());
        $this->assertSame(JsonRpcInternalErrorException::CODE, $response->getError()->getErrorCode());
    }

    public function testGetJsonRpcResponseFromExceptionWithRequestCreatesResponse(): void
    {
        $exception = new \Exception('Test exception');
        $request = new JsonRpcRequest();
        $request->setId(123);

        $response = $this->exceptionHandler->getJsonRpcResponseFromException($exception, $request);

        $this->assertNotNull($response);
        $this->assertSame(123, $response->getId());
        $this->assertNotNull($response->getError());
        $this->assertSame(JsonRpcInternalErrorException::CODE, $response->getError()->getErrorCode());
    }

    public function testGetJsonRpcResponseFromExceptionPreservesExceptionMessage(): void
    {
        $exception = new \RuntimeException('Custom error message');
        $request = new JsonRpcRequest();

        $response = $this->exceptionHandler->getJsonRpcResponseFromException($exception, $request);

        $this->assertNotNull($response);
        $this->assertNotNull($response->getError());
        $errorData = $response->getError()->getErrorData();
        $this->assertArrayHasKey(JsonRpcInternalErrorException::DATA_PREVIOUS_KEY, $errorData);
        $this->assertStringContainsString('Custom error message', $errorData[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]);
    }
}
