<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Serialization;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcResponseNormalizer;

class JsonRpcResponseNormalizerTest extends TestCase
{
    private JsonRpcResponseNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new JsonRpcResponseNormalizer();
    }

    public function testNormalize_withSuccessResponse_returnsNormalizedData(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult(['status' => 'success']);

        $result = $this->normalizer->normalize($response);

        $expectedData = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => ['status' => 'success']
        ];

        $this->assertSame($expectedData, $result);
    }

    public function testNormalize_withErrorResponse_returnsNormalizedErrorData(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        $this->assertArrayHasKey('jsonrpc', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('result', $result);

        $this->assertSame('2.0', $result['jsonrpc']);
        $this->assertSame(1, $result['id']);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
    }

    public function testNormalize_withNotification_returnsNull(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setIsNotification(true);
        $response->setResult(['status' => 'success']);

        $result = $this->normalizer->normalize($response);

        $this->assertNull($result);
    }

    public function testNormalize_withNullResult_includesNullResult(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult(null);

        $result = $this->normalizer->normalize($response);

        $expectedData = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => null
        ];

        $this->assertSame($expectedData, $result);
    }

    public function testNormalize_withStringId_preservesStringId(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId('string-id');
        $response->setResult(['data' => 'test']);

        $result = $this->normalizer->normalize($response);

        $this->assertSame('string-id', $result['id']);
    }

    public function testNormalize_withZeroId_preservesZeroId(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(0);
        $response->setResult(['data' => 'test']);

        $result = $this->normalizer->normalize($response);

        $this->assertSame(0, $result['id']);
    }

    public function testNormalizeError_withErrorDataIncluded_includesDataField(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));

        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        // JsonRpcInternalErrorException 会自动包含前一个异常的信息
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('data', $result['error']);
        $this->assertArrayHasKey('previous', $result['error']['data']);
        $this->assertStringContainsString('Test error', $result['error']['data']['previous']);
    }

    public function testNormalize_withComplexResult_preservesStructure(): void
    {
        $complexResult = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ],
            'meta' => [
                'total' => 2,
                'page' => 1
            ]
        ];

        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult($complexResult);

        $result = $this->normalizer->normalize($response);

        $this->assertSame($complexResult, $result['result']);
    }

    public function testNormalize_withErrorNotification_returnsNull(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setIsNotification(true);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        $this->assertNull($result);
    }

    public function testNormalizeError_withoutErrorData_doesNotIncludeDataField(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));
        
        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcResponseNormalizer::class, 'normalizeError');
        $method->setAccessible(true);
        $result = $method->invoke($this->normalizer, $exception);

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        
        // 如果错误没有数据，不应该包含data字段
        if ($exception->getErrorData() === null) {
            $this->assertArrayNotHasKey('data', $result);
        }
    }
} 