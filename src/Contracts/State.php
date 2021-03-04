<?php

namespace RodrigoPedra\StateMachine\Contracts;

interface State
{
    public function stateKey(): string;

    public function hasSameStateAs(Subject $subject, array $payload = []): bool;
}
