<?php

namespace RodrigoPedra\StateMachine\Model;

use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\Subject;

final class NullState implements State
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public function stateKey(): string
    {
        return \basename(\str_replace('\\', '/', self::class));
    }

    public function hasSameStateAs(Subject $subject, array $payload = []): bool
    {
        return false;
    }

    public static function get(): self
    {
        if (\is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function isNull(State $state): bool
    {
        return $state === self::get();
    }
}
