<?php

namespace RodrigoPedra\StateMachine\Contracts;

interface Subject
{
    public function state(): State;

    public function changeStateTo(State $state): Subject;
}
