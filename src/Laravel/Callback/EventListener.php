<?php

namespace RodrigoPedra\StateMachine\Laravel\Callback;

use Illuminate\Contracts\Container\Container;
use RodrigoPedra\StateMachine\Concerns\WrapsCallback;
use RodrigoPedra\StateMachine\Contracts\TransitionEvent;
use RodrigoPedra\StateMachine\Model\Transition;

final class EventListener
{
    use WrapsCallback;

    private ?string $transitionKey;

    public function __construct(Container $container, ?string $transitionKey, $callback)
    {
        $this->container = $container;
        $this->transitionKey = $transitionKey;
        $this->callback = $this->ensureCallback($callback);
    }

    public function __invoke(TransitionEvent $event)
    {
        if (! $this->listensTo($event->transition())) {
            return null;
        }

        return $this->container->call($this->callback, ['event' => $event]);
    }

    private function listensTo(Transition $transition): bool
    {
        if (\is_null($this->transitionKey)) {
            return true;
        }

        return $this->transitionKey === $transition->transitionKey();
    }
}
