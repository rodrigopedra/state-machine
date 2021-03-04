<?php

namespace RodrigoPedra\StateMachine\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

final class NullDispatcher implements EventDispatcherInterface
{
    /**
     * @param  \RodrigoPedra\StateMachine\Events\TransitionEvent&object  $event
     * @return \RodrigoPedra\StateMachine\Events\TransitionEvent
     */
    public function dispatch(object $event): TransitionEvent
    {
        return $event;
    }
}
