<?php

namespace RodrigoPedra\StateMachine\Model;

use Illuminate\Contracts\Container\Container;
use RodrigoPedra\StateMachine\Callback\TransitionCallback;
use RodrigoPedra\StateMachine\Collections\StatesCollection;
use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Contracts\TransitionCallback as TransitionCallbackContract;
use RodrigoPedra\StateMachine\Laravel\Callback\AbilityTransitionCallback;
use RodrigoPedra\StateMachine\StateMachine;

final class Transition
{
    private string $key;
    private string $subjectKey;
    private StateMachine $stateMachine;
    private StatesCollection $sources;
    private StateContract $lastAppliedState;
    private StateContract $target;
    private ?Container $container;
    private ?TransitionCallbackContract $guard = null;
    private ?TransitionCallbackContract $callback = null;

    public function __construct(StateMachine $stateMachine, string $key, string $subjectKey, ?Container $container)
    {
        $this->key = $key;
        $this->subjectKey = $subjectKey;
        $this->stateMachine = $stateMachine;
        $this->container = $container;
        $this->sources = StatesCollection::make();
        $this->lastAppliedState = NullState::get();
        $this->target = NullState::get();
    }

    public function transitionKey(): string
    {
        return $this->key;
    }

    public function sources(): StatesCollection
    {
        return $this->sources;
    }

    public function target(): StateContract
    {
        if ($this->isLoop()) {
            return $this->lastAppliedState;
        }

        return $this->target;
    }

    public function isLoop(): bool
    {
        return NullState::isNull($this->target);
    }

    public function guard(): ?TransitionCallbackContract
    {
        return $this->guard;
    }

    public function callback(): ?TransitionCallbackContract
    {
        return $this->callback;
    }

    public function canApply(Subject $subject, array $payload = []): bool
    {
        if (! \is_null($this->guard) && $this->guard->call($subject, $payload) === false) {
            return false;
        }

        if ($this->isLoop()) {
            return true;
        }

        $statesAreValid = $this->sources->isEmpty()
            || $this->sources->canApply($subject, $payload);

        return $statesAreValid
            && ! $this->target->hasSameStateAs($subject, $payload);
    }

    public function apply(Subject $subject, array $payload = []): Subject
    {
        if ($this->isLoop()) {
            $this->lastAppliedState = $subject->state();
        }

        $subject->changeStateTo($this->target());

        if (! \is_null($this->callback)) {
            $this->callback->call($subject, $payload);
        }

        return $subject;
    }

    public function withSources(iterable $sources): self
    {
        $sources = StatesCollection::make($sources);

        foreach ($sources as $source) {
            $this->stateMachine->guardState($source);
        }

        $this->sources = $sources;

        return $this;
    }

    public function withTarget($state): self
    {
        $state = State::resolve($state);

        $this->stateMachine->guardState($state);

        $this->target = $state;

        return $this;
    }

    public function withGuard($callback): self
    {
        $this->guard = new TransitionCallback($this->container, $this, $this->subjectKey, $callback);

        return $this;
    }

    public function withCallback($callback): self
    {
        $this->callback = new TransitionCallback($this->container, $this, $this->subjectKey, $callback);

        return $this;
    }

    public function withAbility(?string $ability = null): self
    {
        $this->guard = new AbilityTransitionCallback($this->container, $this, $ability);

        return $this;
    }
}
