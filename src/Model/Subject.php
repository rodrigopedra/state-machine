<?php

namespace RodrigoPedra\StateMachine\Model;

use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Contracts\Subject as SubjectContract;

final class Subject implements SubjectContract
{
    private StateContract $state;

    public function __construct($state)
    {
        $this->state = State::resolve($state);
    }

    public function state(): StateContract
    {
        return $this->state;
    }

    public function changeStateTo(StateContract $state): SubjectContract
    {
        $this->state = $state;

        return $this;
    }
}
