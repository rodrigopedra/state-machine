<?php

namespace RodrigoPedra\StateMachine\Laravel\Concerns;

use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Laravel\StateMachineManager;
use RodrigoPedra\StateMachine\StateMachine;

/**
 * Should implement \RodrigoPedra\StateMachine\Contracts\Subject
 */
trait UsesStateMachine
{
    protected static ?StateMachineManager $manager = null;
    protected ?StateMachine $stateMachine = null;

    abstract public function state(): State;

    abstract public function changeStateTo(State $state): Subject;

    abstract protected static function machine(): string;

    public function stateMachine(): StateMachine
    {
        if (\is_null($this->stateMachine)) {
            $this->stateMachine = static::$manager
                ->machine($this->machine())
                ->withSubject($this);
        }

        return $this->stateMachine;
    }

    public function canApply($transition, array $payload = []): bool
    {
        return $this->stateMachine()->canApply($transition, $payload);
    }

    public function apply($transition, array $payload = []): self
    {
        $this->stateMachine()->apply($transition, $payload);

        return $this;
    }

    public function applySilently($transition, array $payload = []): bool
    {
        return $this->stateMachine()->applySilently($transition, $payload);
    }

    public static function withStateMachineManager(StateMachineManager $manager)
    {
        static::$manager = $manager;
    }
}
