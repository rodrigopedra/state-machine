<?php

namespace RodrigoPedra\StateMachine\Support;

use RodrigoPedra\StateMachine\Concerns\SerializesValue;
use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\StateMachineSerializer;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;

final class ArraySerializer implements StateMachineSerializer
{
    use SerializesValue;

    public function serialize(StateMachine $stateMachine): array
    {
        return [
            'machine' => $stateMachine->key(),
            'initial_state' => $this->serializeState($stateMachine->initialState()),
            'current_state' => $this->serializeState($stateMachine->currentState()),
            'subject' => $this->serializeSubject($stateMachine->subject()),
            'states' => $stateMachine->states()
                ->values()
                ->map(fn (State $state) => $this->serializeState($state))
                ->all(),
            'transition' => $stateMachine->transitions()
                ->values()
                ->map(fn (Transition $transition) => $this->serializeTransition($transition))
                ->all(),
        ];
    }

    private function serializeSubject(Subject $subject): array
    {
        return $this->serializeValue($subject, fn ($subject) => [
            'subject' => \get_class($subject),
            'current_state' => $subject->state()->stateKey(),
        ]);
    }

    private function serializeState(State $state)
    {
        return $this->serializeValue($state, fn ($state) => $state->stateKey());
    }

    private function serializeTransition(Transition $transition): array
    {
        return [
            'transition' => $transition->transitionKey(),
            'is_loop' => $transition->isLoop(),
            'guard' => \strval($transition->guard()),
            'callback' => \strval($transition->callback()),
            'sources' => $transition->sources()
                ->values()
                ->map(fn (State $state) => $this->serializeState($state))
                ->all(),
            'target' => $transition->isLoop()
                ? null
                : $this->serializeState($transition->target()),
        ];
    }
}
