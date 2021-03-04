<?php

namespace RodrigoPedra\StateMachine\Exceptions;

final class UnknownStateException extends StateMachineException
{
    public function __construct(string $stateKey)
    {
        parent::__construct(\vsprintf('Unknown state: "%s".', [
            $stateKey,
        ]));
    }
}
