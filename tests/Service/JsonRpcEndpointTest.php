<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcEndpoint::class)]
#[RunTestsInSeparateProcesses]
final class JsonRpcEndpointTest extends AbstractIntegrationTestCase
{
    private JsonRpcEndpoint $endpoint;

    protected function onSetUp(): void
    {
        $this->endpoint = self::getService(JsonRpcEndpoint::class);
    }

    public function testIndexWithValidPayloadReturnsResponse(): void
    {
        $payload = '{"jsonrpc":"2.0","method":"getServerTime","id":1}';
        $request = Request::create('/', 'POST', [], [], [], [], $payload);

        $result = $this->endpoint->index($payload, $request);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals(1, $decoded['id']);

        // Check if response has either result or error
        $this->assertTrue(
            array_key_exists('result', $decoded) || array_key_exists('error', $decoded),
            'Response should have either result or error key. Actual response: ' . $result
        );
    }

    public function testIndexWithEmptyPayloadReturnsParseError(): void
    {
        $payload = '';
        $request = Request::create('/', 'POST', [], [], [], [], $payload);

        $result = $this->endpoint->index($payload, $request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}', $result);
    }

    public function testIndexWithInvalidJsonPayloadReturnsParseError(): void
    {
        $payload = '{invalid json}';
        $request = Request::create('/', 'POST', [], [], [], [], $payload);

        $result = $this->endpoint->index($payload, $request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}', $result);
    }

    public function testResetResetsStopwatch(): void
    {
        $this->assertInstanceOf(ResetInterface::class, $this->endpoint);

        // Call reset multiple times - if stopwatch is properly managed,
        // these calls should succeed without throwing exceptions
        $this->endpoint->reset();
        $this->endpoint->reset();
        $this->endpoint->reset();
    }
}
