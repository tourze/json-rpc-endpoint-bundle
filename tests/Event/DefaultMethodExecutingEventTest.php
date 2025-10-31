<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Event\MethodExecutingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEndpointBundle\Event\DefaultMethodExecutingEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(DefaultMethodExecutingEvent::class)]
final class DefaultMethodExecutingEventTest extends AbstractEventTestCase
{
    public function testConstructorWithRequestOnly(): void
    {
        $request = new JsonRpcRequest();

        $event = new DefaultMethodExecutingEvent($request);

        $this->assertSame($request, $event->getItem());
        $this->assertNull($event->getResponse());
    }

    public function testConstructorWithRequestAndResponse(): void
    {
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        $event = new DefaultMethodExecutingEvent($request, $response);

        $this->assertSame($request, $event->getItem());
        $this->assertSame($response, $event->getResponse());
    }

    public function testConstructorWithNullResponse(): void
    {
        $request = new JsonRpcRequest();

        $event = new DefaultMethodExecutingEvent($request, null);

        $this->assertSame($request, $event->getItem());
        $this->assertNull($event->getResponse());
    }

    public function testInheritsFromMethodExecutingEvent(): void
    {
        $request = new JsonRpcRequest();
        $event = new DefaultMethodExecutingEvent($request);

        $this->assertInstanceOf(MethodExecutingEvent::class, $event);
    }

    public function testSetAndGetItem(): void
    {
        $request = new JsonRpcRequest();
        $event = new DefaultMethodExecutingEvent($request);

        $newRequest = new JsonRpcRequest();
        $event->setItem($newRequest);

        $this->assertSame($newRequest, $event->getItem());
    }

    public function testSetAndGetResponse(): void
    {
        $request = new JsonRpcRequest();
        $event = new DefaultMethodExecutingEvent($request);

        $response = new JsonRpcResponse();
        $event->setResponse($response);

        $this->assertSame($response, $event->getResponse());
    }

    public function testSetResponseToNull(): void
    {
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();
        $event = new DefaultMethodExecutingEvent($request, $response);

        $event->setResponse(null);

        $this->assertNull($event->getResponse());
    }

    public function testConstructorCallsInheritedSetters(): void
    {
        $request = new JsonRpcRequest();
        $response = new JsonRpcResponse();

        // Create event and verify that the constructor properly calls the inherited setters
        $event = new DefaultMethodExecutingEvent($request, $response);

        // Verify that the setters work as expected by changing the values
        $newRequest = new JsonRpcRequest();
        $newResponse = new JsonRpcResponse();

        $event->setItem($newRequest);
        $event->setResponse($newResponse);

        $this->assertSame($newRequest, $event->getItem());
        $this->assertSame($newResponse, $event->getResponse());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(DefaultMethodExecutingEvent::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testRequestParameterIsRequired(): void
    {
        $request = new JsonRpcRequest();
        $event = new DefaultMethodExecutingEvent($request);

        // The request parameter is required in constructor, so we verify it's properly set
        $this->assertSame($request, $event->getItem());
    }

    public function testResponseParameterIsOptional(): void
    {
        $request = new JsonRpcRequest();

        // Test that response parameter defaults to null when not provided
        $event = new DefaultMethodExecutingEvent($request);

        $this->assertNull($event->getResponse());
    }

    public function testCanModifyResponseAfterConstruction(): void
    {
        $request = new JsonRpcRequest();
        $event = new DefaultMethodExecutingEvent($request);

        $this->assertNull($event->getResponse());

        $response = new JsonRpcResponse();
        $event->setResponse($response);

        $this->assertSame($response, $event->getResponse());

        $event->setResponse(null);

        $this->assertNull($event->getResponse());
    }
}
