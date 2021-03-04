<?php

namespace RodrigoPedra\StateMachine\Events;

use RodrigoPedra\StateMachine\Contracts\HaltableEvent;

final class ApplyingTransition extends TransitionEvent implements HaltableEvent
{
    private bool $wasHalted = false;

    public function halted(): bool
    {
        return $this->wasHalted;
    }

    public function halt(): self
    {
        $this->wasHalted = true;

        return $this;
    }
}
