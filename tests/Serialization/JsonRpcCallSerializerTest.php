<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Serialization;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Exception\JsonRpcParseErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallResponseNormalizer;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallSerializer;

class JsonRpcCallSerializerTest extends TestCase
{
    private JsonRpcCallSerializer $serializer;
    private JsonRpcCallDenormalizer|MockObject $callDenormalizer;
    private JsonRpcCallResponseNormalizer|MockObject $callResponseNormalizer;

    protected function setUp(): void
    {
        $this->callDenormalizer = $this->createMock(JsonRpcCallDenormalizer::class);
        $this->callResponseNormalizer = $this->createMock(JsonRpcCallResponseNormalizer::class);
        $this->serializer = new JsonRpcCallSerializer(
            $this->callDenormalizer,
            $this->callResponseNormalizer
        );
    }

    public function testDeserialize_withValidJson_returnsCallRequest(): void
    {
        $content = '{"jsonrpc":"2.0","method":"test","id":1}';
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];
        $callRequest = new JsonRpcCallRequest();

        $this->callDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($decodedContent)
            ->willReturn($callRequest);

        $result = $this->serializer->deserialize($content);

        $this->assertSame($callRequest, $result);
    }

    public function testDeserialize_withInvalidJson_throwsParseErrorException(): void
    {
        $content = '{invalid json}';

        $this->expectException(JsonRpcParseErrorException::class);
        $this->serializer->deserialize($content);
    }

    public function testDeserialize_withEmptyArray_throwsInvalidRequestException(): void
    {
        $content = '[]';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->deserialize($content);
    }

    public function testDeserialize_withNonArrayContent_throwsInvalidRequestException(): void
    {
        $content = '"string"';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->deserialize($content);
    }

    public function testSerialize_withValidResponse_returnsJsonString(): void
    {
        $callResponse = $this->createMock(JsonRpcCallResponse::class);
        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn($normalizedData);

        $result = $this->serializer->serialize($callResponse);

        $expectedJson = json_encode($normalizedData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $this->assertSame($expectedJson, $result);
    }

    public function testSerialize_withNullNormalizedData_returnsNullJsonString(): void
    {
        $callResponse = $this->createMock(JsonRpcCallResponse::class);

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn(null);

        $result = $this->serializer->serialize($callResponse);

        $this->assertSame('null', $result);
    }

    public function testEncode_withValidData_returnsJsonString(): void
    {
        $data = ['test' => 'value'];
        $result = $this->serializer->encode($data);

        $this->assertSame('{"test":"value"}', $result);
    }

    public function testDecode_withValidJson_returnsDecodedArray(): void
    {
        $json = '{"test":"value"}';
        $result = $this->serializer->decode($json);

        $this->assertSame(['test' => 'value'], $result);
    }

    public function testDecode_withInvalidJson_throwsParseErrorException(): void
    {
        $json = '{invalid}';

        $this->expectException(JsonRpcParseErrorException::class);
        $this->serializer->decode($json);
    }

    public function testDecode_withEmptyArray_throwsInvalidRequestException(): void
    {
        $json = '[]';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->decode($json);
    }

    public function testDenormalize_withValidData_returnsCallRequest(): void
    {
        $data = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];
        $callRequest = new JsonRpcCallRequest();

        $this->callDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($data)
            ->willReturn($callRequest);

        $result = $this->serializer->denormalize($data);

        $this->assertSame($callRequest, $result);
    }

    public function testNormalize_withValidResponse_returnsNormalizedData(): void
    {
        $callResponse = $this->createMock(JsonRpcCallResponse::class);
        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn($normalizedData);

        $result = $this->serializer->normalize($callResponse);

        $this->assertSame($normalizedData, $result);
    }
}
