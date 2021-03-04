<?php

namespace RodrigoPedra\StateMachine\Events;

use RodrigoPedra\StateMachine\Concerns\SerializesValue;
use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Contracts\TransitionEvent as TransitionEventContract;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;

abstract class TransitionEvent implements TransitionEventContract
{
    use SerializesValue;

    protected StateMachine $stateMachine;
    protected Transition $transition;
    protected array $payload;

    public function __construct(StateMachine $stateMachine, Transition $transition, array $payload)
    {
        $this->stateMachine = $stateMachine;
        $this->transition = $transition;
        $this->payload = $payload;
    }

    public function stateMachine(): StateMachine
    {
        return $this->stateMachine;
    }

    public function subject(): Subject
    {
        return $this->stateMachine->subject();
    }

    public function currentState(): State
    {
        return $this->stateMachine->currentState();
    }

    public function transition(): Transition
    {
        return $this->transition;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function toArray(): array
    {
        return $this->serializeValue($this->subject(), fn ($subject) => [
            $this->stateMachine->subjectKey() => $subject,
            'payload' => $this->payload,
            'transition' => $this->transition()->transitionKey(),
            'currentState' => $this->currentState()->stateKey(),
        ]);
    }

    public function toJson($options = 0)
    {
        return \json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
