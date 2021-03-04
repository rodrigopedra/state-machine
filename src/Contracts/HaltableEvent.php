<?php

namespace RodrigoPedra\StateMachine\Contracts;

interface HaltableEvent extends TransitionEvent
{
    public function halted(): bool;

    public function halt(): HaltableEvent;
}
