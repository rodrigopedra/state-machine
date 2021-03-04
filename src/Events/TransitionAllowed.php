<?php

namespace RodrigoPedra\StateMachine\Events;

use RodrigoPedra\StateMachine\Contracts\TransitionVerifiedEvent;

final class TransitionAllowed extends TransitionEvent implements TransitionVerifiedEvent
{
    public function allowed(): bool
    {
        return true;
    }

    public function rejected(): bool
    {
        return false;
    }
}
