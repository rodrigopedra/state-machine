<?php

namespace RodrigoPedra\StateMachine\Model;

use RodrigoPedra\StateMachine\Concerns\ComparesWithSubject;
use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Exceptions\UnknownStateException;

final class State implements StateContract
{
    use ComparesWithSubject;

    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function stateKey(): string
    {
        return $this->key;
    }

    public static function resolve($state): StateContract
    {
        if (\is_null($state)) {
            $state = NullState::get();
        }

        if (! $state instanceof StateContract) {
            $state = new self(\strval($state));
        }

        if (NullState::isNull($state)) {
            throw new UnknownStateException($state);
        }

        return $state;
    }
}
