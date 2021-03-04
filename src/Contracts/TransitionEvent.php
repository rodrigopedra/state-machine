<?php

namespace RodrigoPedra\StateMachine\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;

interface TransitionEvent extends Arrayable, Jsonable, \JsonSerializable
{
    public function stateMachine(): StateMachine;

    public function subject(): Subject;

    public function currentState(): State;

    public function transition(): Transition;

    public function payload(): array;
}
