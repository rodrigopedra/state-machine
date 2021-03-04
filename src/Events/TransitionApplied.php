<?php

namespace RodrigoPedra\StateMachine\Events;

use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;

final class TransitionApplied extends TransitionEvent
{
    private State $previousState;

    public function __construct(
        StateMachine $stateMachine,
        Transition $transition,
        State $previousState,
        array $payload
    ) {
        parent::__construct($stateMachine, $transition, $payload);

        $this->previousState = $previousState;
    }

    public function previousState(): State
    {
        return $this->previousState;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['previousState'] = $this->previousState->stateKey();

        return $array;
    }
}
