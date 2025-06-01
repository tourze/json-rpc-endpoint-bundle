<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Serialization;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcRequestDenormalizer;

class JsonRpcRequestDenormalizerTest extends TestCase
{
    private JsonRpcRequestDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new JsonRpcRequestDenormalizer();
    }

    public function testDenormalize_withValidRequest_returnsJsonRpcRequest(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => ['param1' => 'value1'],
            'id' => 1
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertInstanceOf(JsonRpcRequest::class, $result);
        $this->assertEquals('2.0', $result->getJsonrpc());
        $this->assertEquals('test.method', $result->getMethod());
        $this->assertEquals(['param1' => 'value1'], $result->getParams()->toArray());
        // ID 会被转换为整数，但 getId() 返回实际存储的类型
        $this->assertEquals(1, $result->getId());
    }

    public function testDenormalize_withStringId_preservesStringType(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => 'string-id'
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals('string-id', $result->getId());
    }

    public function testDenormalize_withNumericStringId_convertsToInt(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => '123'
        ];

        $result = $this->denormalizer->denormalize($data);

        // 数字字符串会被转换为整数
        $this->assertEquals(123, $result->getId());
    }

    public function testDenormalize_withoutId_setsNullId(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => []
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertNull($result->getId());
        $this->assertTrue($result->isNotification());
    }

    public function testDenormalize_withoutParams_setsEmptyParams(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals([], $result->getParams()->toArray());
    }

    public function testDenormalize_withMissingJsonrpc_throwsException(): void
    {
        $data = [
            'method' => 'test.method',
            'id' => 1
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"jsonrpc" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalize_withMissingMethod_throwsException(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => 1
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"method" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalize_withZeroId_bindsCorrectly(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => 0
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals(0, $result->getId());
        $this->assertFalse($result->isNotification());
    }

    public function testDenormalize_withNullId_treatsAsNotification(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => null
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertNull($result->getId());
        $this->assertTrue($result->isNotification());
    }

    public function testDenormalize_withNonArrayData_throwsException(): void
    {
        $data = 'not an array';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('Item must be an array');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalize_withNonArrayParams_throwsException(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1,
            'params' => 'not an array'
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('Parameter list must be an array');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalize_withComplexParams_bindsCorrectly(): void
    {
        $params = [
            'user' => ['name' => 'John', 'age' => 30],
            'data' => [1, 2, 3],
            'config' => ['debug' => true]
        ];
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1,
            'params' => $params
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals($params, $result->getParams()->toArray());
    }
} 