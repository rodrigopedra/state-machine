<?php

namespace RodrigoPedra\StateMachine\Events;

use RodrigoPedra\StateMachine\Contracts\TransitionVerifiedEvent;

final class TransitionRejected extends TransitionEvent implements TransitionVerifiedEvent
{
    public function allowed(): bool
    {
        return false;
    }

    public function rejected(): bool
    {
        return true;
    }
}
