<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Serialization;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallResponseNormalizer;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcResponseNormalizer;

class JsonRpcCallResponseNormalizerTest extends TestCase
{
    private JsonRpcCallResponseNormalizer $normalizer;
    private JsonRpcResponseNormalizer&MockObject $responseNormalizer;

    protected function setUp(): void
    {
        $this->responseNormalizer = $this->createMock(JsonRpcResponseNormalizer::class);
        $this->normalizer = new JsonRpcCallResponseNormalizer($this->responseNormalizer);
    }

    public function testNormalize_withSingleResponse_returnsNormalizedResponse(): void
    {
        /** @var JsonRpcResponse&MockObject $response */
        $response = $this->createMock(JsonRpcResponse::class);
        $response->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->responseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($response)
            ->willReturn($normalizedData);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertSame($normalizedData, $result);
    }

    public function testNormalize_withBatchResponse_returnsArrayOfNormalizedResponses(): void
    {
        /** @var JsonRpcResponse&MockObject $response1 */
        $response1 = $this->createMock(JsonRpcResponse::class);
        $response1->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        /** @var JsonRpcResponse&MockObject $response2 */
        $response2 = $this->createMock(JsonRpcResponse::class);
        $response2->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $normalizedData1 = ['jsonrpc' => '2.0', 'result' => 'test1', 'id' => 1];
        $normalizedData2 = ['jsonrpc' => '2.0', 'result' => 'test2', 'id' => 2];

        $this->responseNormalizer->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnOnConsecutiveCalls($normalizedData1, $normalizedData2);

        $result = $this->normalizer->normalize($callResponse);
        $this->assertCount(2, $result);
        $this->assertSame($normalizedData1, $result[0]);
        $this->assertSame($normalizedData2, $result[1]);
    }

    public function testNormalize_withNotificationOnlyResponse_returnsNull(): void
    {
        /** @var JsonRpcResponse&MockObject $response */
        $response = $this->createMock(JsonRpcResponse::class);
        $response->expects($this->once())
            ->method('isNotification')
            ->willReturn(true);

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize');

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalize_withMixedNotificationAndNormalResponse_returnsOnlyNormalResponse(): void
    {
        /** @var JsonRpcResponse&MockObject $notificationResponse */
        $notificationResponse = $this->createMock(JsonRpcResponse::class);
        $notificationResponse->expects($this->once())
            ->method('isNotification')
            ->willReturn(true);

        /** @var JsonRpcResponse&MockObject $normalResponse */
        $normalResponse = $this->createMock(JsonRpcResponse::class);
        $normalResponse->expects($this->once())
            ->method('isNotification')
            ->willReturn(false);

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($notificationResponse);
        $callResponse->addResponse($normalResponse);

        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->responseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($normalResponse)
            ->willReturn($normalizedData);

        $result = $this->normalizer->normalize($callResponse);
        $this->assertCount(1, $result);
        $this->assertSame($normalizedData, $result[0]);
    }

    public function testNormalize_withBatchNotificationOnlyResponses_returnsNull(): void
    {
        /** @var JsonRpcResponse&MockObject $response1 */
        $response1 = $this->createMock(JsonRpcResponse::class);
        $response1->expects($this->once())
            ->method('isNotification')
            ->willReturn(true);

        /** @var JsonRpcResponse&MockObject $response2 */
        $response2 = $this->createMock(JsonRpcResponse::class);
        $response2->expects($this->once())
            ->method('isNotification')
            ->willReturn(true);

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize');

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalize_withEmptyResponse_returnsNull(): void
    {
        $callResponse = new JsonRpcCallResponse(false);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize');

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }
} 