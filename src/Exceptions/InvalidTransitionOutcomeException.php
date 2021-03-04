<?php

namespace RodrigoPedra\StateMachine\Exceptions;

use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Model\Transition;

final class InvalidTransitionOutcomeException extends StateMachineException
{
    public function __construct(Transition $transition, Subject $subject)
    {
        parent::__construct(\vsprintf('Transition "%s" lead to an unexpected outcome. Expected: "%s", got: "%s"', [
            $transition->transitionKey(),
            $transition->target()->stateKey(),
            $subject->state()->stateKey(),
        ]));
    }
}
