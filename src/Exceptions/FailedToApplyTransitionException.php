<?php

namespace RodrigoPedra\StateMachine\Exceptions;

use RodrigoPedra\StateMachine\Model\Transition;

final class FailedToApplyTransitionException extends StateMachineException
{
    public function __construct(Transition $transition)
    {
        parent::__construct(\vsprintf('Failed to apply transition: "%s".', [
            $transition->transitionKey(),
        ]));
    }
}
