<?php

namespace RodrigoPedra\StateMachine\Laravel\Builder;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Exceptions\UnresolvableTransitionException;
use RodrigoPedra\StateMachine\Laravel\Callback\EventListener;
use RodrigoPedra\StateMachine\Laravel\Events\IlluminateDispatcher;
use RodrigoPedra\StateMachine\Model\State;
use RodrigoPedra\StateMachine\StateMachine;

final class StateMachineBuilder
{
    private string $key;
    private array $config;

    public function __construct(string $key, array $config)
    {
        $this->key = $key;
        $this->config = $config;
    }

    public function __invoke(Container $container): StateMachine
    {
        $dispatcher = $container->make(Dispatcher::class);

        $stateMachine = StateMachine::fromState(
            $this->key,
            $this->config['initial_state'],
            new IlluminateDispatcher($dispatcher),
        );

        $stateMachine->withContainer($container);

        foreach ($this->config['states'] as $key => $value) {
            $stateMachine->addState($this->resolveState($container, $key, $value));
        }

        foreach ($this->config['transitions'] as $key => $config) {
            try {
                $this->buildTransition($stateMachine, $key, $config);
            } catch (\Throwable $exception) {
                throw new UnresolvableTransitionException($key);
            }
        }

        $events = $this->config['listeners'] ?? [];

        foreach ($events as $key => $listeners) {
            foreach ($listeners as $config) {
                $dispatcher->listen($key, $this->makeListener($container, $config));
            }
        }

        return $stateMachine;
    }

    private function buildTransition(StateMachine $stateMachine, string $key, array $config)
    {
        $transition = $stateMachine->addTransition($key);

        $sources = $config['sources'] ?? false;

        if ($sources) {
            $transition->withSources($sources);
        }

        $target = $config['target'] ?? false;

        if ($target) {
            $transition->withTarget($target);
        }

        $guard = $config['guard'] ?? false;

        if ($guard) {
            $transition->withGuard($guard);
        }

        $ability = $config['ability'] ?? false;

        if ($ability) {
            $transition->withAbility($ability);
        }

        $callback = $config['callback'] ?? false;

        if ($callback) {
            $transition->withCallback($callback);
        }
    }

    private function resolveState(Container $container, $key, $value): StateContract
    {
        if (\is_string($value)) {
            $key = $value;
            $value = [];
        }

        if (\class_exists($key)) {
            return $container->make($key, $value);
        }

        return State::resolve($key);
    }

    private function makeListener(Container $container, array $listener): EventListener
    {
        $transition = $listener['on'] ?? null;
        $callback = $listener['handler'];

        if (\is_string($callback) && \method_exists($callback, 'handle')) {
            $callback = [$callback, 'handle'];
        }

        return new EventListener($container, $transition, $callback);
    }
}
