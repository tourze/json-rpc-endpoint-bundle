<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCEndpointBundle\EventSubscriber\JsonRpcResultListener;
use Tourze\JsonRPCEndpointBundle\Traits\AppendJsonRpcResultAware;
use Tourze\UserEventBundle\Event\UserInteractionEvent;

class JsonRpcResultListenerTest extends TestCase
{
    private JsonRpcResultListener $listener;

    protected function setUp(): void
    {
        $this->listener = new JsonRpcResultListener();
    }

    public function testInvoke_withNonAppendJsonRpcResultAwareEvent_doesNotModifyResult(): void
    {
        $event = $this->createMock(UserInteractionEvent::class);

        $this->listener->__invoke($event);

        $this->assertSame([], $this->listener->getResult());
    }

    public function testInvoke_withAppendJsonRpcResultAwareEvent_mergesResults(): void
    {
        $event = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event->setJsonRpcResult(['key1' => 'value1', 'key2' => 'value2']);

        $this->listener->__invoke($event);

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $this->listener->getResult());
    }

    public function testInvoke_withMultipleAppendJsonRpcResultAwareEvents_accumulatesResults(): void
    {
        $event1 = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event1->setJsonRpcResult(['key1' => 'value1']);

        $event2 = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event2->setJsonRpcResult(['key2' => 'value2']);

        $this->listener->__invoke($event1);
        $this->listener->__invoke($event2);

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $this->listener->getResult());
    }

    public function testInvoke_withOverlappingKeys_lastEventWins(): void
    {
        $event1 = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event1->setJsonRpcResult(['key' => 'value1']);

        $event2 = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event2->setJsonRpcResult(['key' => 'value2']);

        $this->listener->__invoke($event1);
        $this->listener->__invoke($event2);

        $this->assertSame(['key' => 'value2'], $this->listener->getResult());
    }

    public function testReset_clearsResults(): void
    {
        $event = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event->setJsonRpcResult(['key' => 'value']);

        $this->listener->__invoke($event);
        $this->assertNotEmpty($this->listener->getResult());

        $this->listener->reset();

        $this->assertSame([], $this->listener->getResult());
    }

    public function testSetResult_replacesPreviousResults(): void
    {
        $event = new class extends UserInteractionEvent {
            use AppendJsonRpcResultAware;
        };
        $event->setJsonRpcResult(['key1' => 'value1']);

        $this->listener->__invoke($event);
        $this->assertSame(['key1' => 'value1'], $this->listener->getResult());

        $this->listener->setResult(['key2' => 'value2']);

        $this->assertSame(['key2' => 'value2'], $this->listener->getResult());
    }
}
