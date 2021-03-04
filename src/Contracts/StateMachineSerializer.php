<?php

namespace RodrigoPedra\StateMachine\Contracts;

use RodrigoPedra\StateMachine\StateMachine;

interface StateMachineSerializer
{
    public function serialize(StateMachine $stateMachine);
}
