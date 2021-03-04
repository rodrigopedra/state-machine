<?php

namespace RodrigoPedra\StateMachine\Laravel;

use Illuminate\Support\Manager;
use RodrigoPedra\StateMachine\StateMachine;

class StateMachineManager extends Manager
{
    public function driver($driver = null): StateMachine
    {
        /** @var \RodrigoPedra\StateMachine\StateMachine $stateMachine */
        $stateMachine = parent::driver($driver);

        // Ensure we build a new state machine every time
        // as we want it to start on its configured initial state
        // and allow to have two instances with different subjects.
        unset($this->drivers[$driver]);

        return $stateMachine;
    }

    public function machine(?string $machine = null): StateMachine
    {
        return $this->driver($machine);
    }

    public function getDefaultDriver(): ?string
    {
        return $this->config->get('state-machines.default');
    }
}
