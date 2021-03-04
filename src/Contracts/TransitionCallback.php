<?php

namespace RodrigoPedra\StateMachine\Contracts;

interface TransitionCallback
{
    public function call(Subject $subject, array $payload = []);

    public function __toString();
}
