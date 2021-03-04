<?php

namespace RodrigoPedra\StateMachine\Concerns;

use RodrigoPedra\StateMachine\Contracts\Subject;

trait ComparesWithSubject
{
    abstract public function stateKey(): string;

    public function hasSameStateAs(Subject $subject, array $payload = []): bool
    {
        $state = $subject->state();

        return $state instanceof static
            && $this->stateKey() === $state->stateKey();
    }
}
