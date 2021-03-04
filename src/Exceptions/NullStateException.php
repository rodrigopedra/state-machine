<?php

namespace RodrigoPedra\StateMachine\Exceptions;

final class NullStateException extends StateMachineException
{
    public function __construct()
    {
        parent::__construct('Cannot add a null state');
    }
}
