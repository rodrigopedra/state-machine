<?php

namespace RodrigoPedra\StateMachine;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use RodrigoPedra\StateMachine\Collections\StatesCollection;
use RodrigoPedra\StateMachine\Collections\TransitionsCollection;
use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Contracts\Subject as SubjectContract;
use RodrigoPedra\StateMachine\Events\ApplyingTransition;
use RodrigoPedra\StateMachine\Events\NullDispatcher;
use RodrigoPedra\StateMachine\Events\TransitionAllowed;
use RodrigoPedra\StateMachine\Events\TransitionApplied;
use RodrigoPedra\StateMachine\Events\TransitionHalted;
use RodrigoPedra\StateMachine\Events\TransitionRejected;
use RodrigoPedra\StateMachine\Exceptions\FailedToApplyTransitionException;
use RodrigoPedra\StateMachine\Exceptions\InvalidTransitionOutcomeException;
use RodrigoPedra\StateMachine\Exceptions\NullStateException;
use RodrigoPedra\StateMachine\Exceptions\StateMachineException;
use RodrigoPedra\StateMachine\Exceptions\UnknownStateException;
use RodrigoPedra\StateMachine\Laravel\Events\IlluminateDispatcher;
use RodrigoPedra\StateMachine\Model\NullState;
use RodrigoPedra\StateMachine\Model\State;
use RodrigoPedra\StateMachine\Model\Subject;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\Support\ArraySerializer;
use RodrigoPedra\StateMachine\Support\DotSerializer;

final class StateMachine implements Arrayable, Jsonable, \JsonSerializable
{
    private string $key;
    private string $subjectKey = 'subject';
    private SubjectContract $subject;
    private StateContract $initialState;
    private StatesCollection $states;
    private TransitionsCollection $transitions;
    private EventDispatcherInterface $dispatcher;
    private ?ContainerContract $container = null;

    private function __construct(string $key, SubjectContract $subject, EventDispatcherInterface $dispatcher)
    {
        $this->key = $key;
        $this->states = StatesCollection::make();
        $this->transitions = TransitionsCollection::make();
        $this->dispatcher = $dispatcher;

        $this->withSubject($subject);
    }

    public function withSubject(SubjectContract $subject): self
    {
        $state = $subject->state();
        $this->subject = $subject;
        $this->initialState = $state;
        $this->states->put($state->stateKey(), $state);

        return $this;
    }

    public function withContainer(ContainerContract $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function subjectAs(string $subjectKey): self
    {
        $this->subjectKey = $subjectKey;

        return $this;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function subjectKey(): string
    {
        return $this->subjectKey;
    }

    public function subject(): SubjectContract
    {
        return $this->subject;
    }

    public function initialState(): StateContract
    {
        return $this->initialState;
    }

    public function currentState(): StateContract
    {
        return $this->subject->state();
    }

    public function states(): StatesCollection
    {
        return StatesCollection::make($this->states->all());
    }

    public function transitions(): TransitionsCollection
    {
        return TransitionsCollection::make($this->transitions->all());
    }

    public function transitionsAvailable(array $payload = []): TransitionsCollection
    {
        return $this->transitions->available($this->subject, $payload);
    }

    public function addState($state): self
    {
        $state = State::resolve($state);

        $this->states->put($state->stateKey(), $state);

        return $this;
    }

    public function addTransition(string $key): Transition
    {
        $transition = new Transition($this, $key, $this->subjectKey, $this->container);

        $this->transitions->put($transition->transitionKey(), $transition);

        return $transition;
    }

    public function canApply($transition, array $payload = []): bool
    {
        $transition = $this->transitions->findOrFail($transition);

        $allowed = $transition->canApply($this->subject);

        if ($allowed) {
            $this->dispatcher->dispatch(new TransitionAllowed($this, $transition, $payload));
        } else {
            $this->dispatcher->dispatch(new TransitionRejected($this, $transition, $payload));
        }

        return $allowed;
    }

    public function apply($transition, array $payload = []): self
    {
        $transition = $this->transitions->findOrFail($transition);

        if (! $this->canApply($transition, $payload)) {
            throw new FailedToApplyTransitionException($transition);
        }

        $event = $this->dispatcher->dispatch(new ApplyingTransition($this, $transition, $payload));

        if ($event->halted()) {
            $this->dispatcher->dispatch(new TransitionHalted($this, $transition, $payload));

            return $this;
        }

        $previousState = $this->currentState();

        $this->applyTransition($transition, $payload);

        $this->dispatcher->dispatch(new TransitionApplied($this, $transition, $previousState, $payload));

        return $this;
    }

    public function applySilently($transition, array $payload = []): bool
    {
        try {
            $this->apply($transition, $payload);
        } catch (StateMachineException $exception) {
            return false;
        }

        return true;
    }

    public function guardState(StateContract $state)
    {
        if (NullState::isNull($state)) {
            throw new NullStateException();
        }

        if (! $this->states->has($state->stateKey())) {
            throw new UnknownStateException($state);
        }
    }

    public function toArray(): array
    {
        $serializer = new ArraySerializer();

        return $serializer->serialize($this);
    }

    public function toJson($options = 0): string
    {
        return \json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toDot(string $layout = 'TB', string $shape = 'circle'): string
    {
        $serializer = new DotSerializer($layout, $shape);

        return $serializer->serialize($this);
    }

    private function applyTransition(Transition $transition, array $payload = [])
    {
        $subject = $transition->apply($this->subject, $payload);

        if (! $transition->target()->hasSameStateAs($subject, $payload)) {
            throw new InvalidTransitionOutcomeException($transition, $subject);
        }

        $this->subject = $subject;
    }

    public static function fromState(string $name, $state, ?EventDispatcherInterface $dispatcher = null): self
    {
        return self::make($name, new Subject($state), $dispatcher);
    }

    public static function make(
        string $name,
        SubjectContract $subject,
        ?EventDispatcherInterface $dispatcher = null
    ): self {
        $container = null;

        if (\class_exists(Container::class)) {
            $container = Container::getInstance();
        }

        if (\is_null($dispatcher)) {
            $dispatcher = ! \is_null($container) && \class_exists(Dispatcher::class)
                ? new IlluminateDispatcher($container->make(Dispatcher::class))
                : new NullDispatcher();
        }

        $stateMachine = new self($name, $subject, $dispatcher);

        if (! \is_null($container)) {
            $stateMachine->withContainer($container);
        }

        return $stateMachine;
    }
}
