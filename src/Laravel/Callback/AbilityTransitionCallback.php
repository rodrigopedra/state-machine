<?php

namespace RodrigoPedra\StateMachine\Laravel\Callback;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Contracts\TransitionCallback;
use RodrigoPedra\StateMachine\Model\Transition;

final class AbilityTransitionCallback implements TransitionCallback
{
    private Container $container;
    private Transition $transition;
    private string $ability;

    public function __construct(Container $container, Transition $transition, ?string $ability)
    {
        $this->container = $container;
        $this->transition = $transition;

        if (\is_null($ability)) {
            $this->ability = \strval(Str::of($transition->transitionKey())->lower()->camel());
        } else {
            $this->ability = $ability;
        }
    }

    public function call(Subject $subject, array $payload = []): bool
    {
        $gate = $this->container->make(Gate::class);

        $payload = \array_values($payload);

        return $gate->allows($this->ability, [$subject, $this->transition, ...$payload]);
    }

    public function ability(): string
    {
        return $this->ability;
    }

    public function __toString(): string
    {
        return 'Gate -> ' . $this->ability;
    }
}
