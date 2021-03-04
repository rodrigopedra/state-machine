<?php

namespace RodrigoPedra\StateMachine\Exceptions;

final class UnresolvableTransitionException extends StateMachineException
{
    public function __construct(?string $transitionKey)
    {
        parent::__construct(\vsprintf('Failed to resolve transition: "%s".', [
            $transitionKey,
        ]));
    }
}
