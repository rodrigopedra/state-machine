<?php

namespace RodrigoPedra\StateMachine\Laravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use RodrigoPedra\StateMachine\Contracts\HaltableEvent;
use RodrigoPedra\StateMachine\Events\TransitionEvent;

final class IlluminateDispatcher implements EventDispatcherInterface
{
    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param  \RodrigoPedra\StateMachine\Events\TransitionEvent&object  $event
     * @return \RodrigoPedra\StateMachine\Events\TransitionEvent
     */
    public function dispatch(object $event): TransitionEvent
    {
        $isHaltable = $event instanceof HaltableEvent;

        $response = $this->dispatcher->dispatch($event, [], $isHaltable);

        if ($response === false && $isHaltable) {
            /** @var  \RodrigoPedra\StateMachine\Contracts\HaltableEvent $event */
            $event->halt();
        }

        return $event;
    }
}
