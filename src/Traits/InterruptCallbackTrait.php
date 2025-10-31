<?php

declare(strict_types=1);

namespace Tourze\JsonRPCEndpointBundle\Traits;

/**
 * 在一些情景中，我们可能希望第三方拦截我们的业务，此时就可以使用这个Trait
 */
trait InterruptCallbackTrait
{
    /**
     * @var callable|null 可执行的回调函数
     */
    private $interruptCallback;

    public function getInterruptCallback(): ?callable
    {
        return $this->interruptCallback;
    }

    public function setInterruptCallback(?callable $interruptCallback): void
    {
        $this->interruptCallback = $interruptCallback;
    }
}
