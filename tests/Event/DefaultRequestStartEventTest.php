<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;
use Tourze\JsonRPCEndpointBundle\Event\DefaultRequestStartEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(DefaultRequestStartEvent::class)]
final class DefaultRequestStartEventTest extends AbstractEventTestCase
{
    public function testConstructorWithNullRequestAndEmptyPayload(): void
    {
        $event = new DefaultRequestStartEvent();

        $this->assertNull($event->getRequest());
        $this->assertSame('', $event->getPayload());
    }

    public function testConstructorWithRequestAndPayload(): void
    {
        $request = new Request();
        $payload = '{"jsonrpc":"2.0","method":"test","id":1}';

        $event = new DefaultRequestStartEvent($request, $payload);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($payload, $event->getPayload());
    }

    public function testConstructorWithOnlyRequest(): void
    {
        $request = new Request();

        $event = new DefaultRequestStartEvent($request);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame('', $event->getPayload());
    }

    public function testConstructorWithOnlyPayload(): void
    {
        $payload = '{"jsonrpc":"2.0","method":"test","id":1}';

        $event = new DefaultRequestStartEvent(null, $payload);

        $this->assertNull($event->getRequest());
        $this->assertSame($payload, $event->getPayload());
    }

    public function testInheritsFromRequestStartEvent(): void
    {
        $event = new DefaultRequestStartEvent();

        $this->assertInstanceOf(RequestStartEvent::class, $event);
    }

    public function testSetAndGetRequest(): void
    {
        $event = new DefaultRequestStartEvent();
        $request = new Request();

        $event->setRequest($request);

        $this->assertSame($request, $event->getRequest());
    }

    public function testSetAndGetPayload(): void
    {
        $event = new DefaultRequestStartEvent();
        $payload = '{"jsonrpc":"2.0","method":"test","params":[],"id":1}';

        $event->setPayload($payload);

        $this->assertSame($payload, $event->getPayload());
    }

    public function testSetRequestToNull(): void
    {
        $event = new DefaultRequestStartEvent(new Request());

        $event->setRequest(null);

        $this->assertNull($event->getRequest());
    }

    public function testSetEmptyPayload(): void
    {
        $event = new DefaultRequestStartEvent(null, 'initial payload');

        $event->setPayload('');

        $this->assertSame('', $event->getPayload());
    }

    public function testConstructorCallsInheritedSetters(): void
    {
        $request = new Request();
        $payload = '{"test": true}';

        // Create event and verify that the constructor properly calls the inherited setters
        $event = new DefaultRequestStartEvent($request, $payload);

        // Verify that the setters work as expected by changing the values
        $newRequest = new Request(['param' => 'value']);
        $newPayload = '{"modified": true}';

        $event->setRequest($newRequest);
        $event->setPayload($newPayload);

        $this->assertSame($newRequest, $event->getRequest());
        $this->assertSame($newPayload, $event->getPayload());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(DefaultRequestStartEvent::class);

        $this->assertTrue($reflection->isFinal());
    }
}
