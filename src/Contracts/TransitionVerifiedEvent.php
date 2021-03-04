<?php

namespace RodrigoPedra\StateMachine\Contracts;

interface TransitionVerifiedEvent extends TransitionEvent
{
    public function allowed(): bool;

    public function rejected(): bool;
}
