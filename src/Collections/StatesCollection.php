<?php

namespace RodrigoPedra\StateMachine\Collections;

use Illuminate\Support\Collection;
use RodrigoPedra\StateMachine\Concerns\ForwardsToBaseCollection;
use RodrigoPedra\StateMachine\Contracts\State as StateContract;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Model\State;

final class StatesCollection extends Collection
{
    use ForwardsToBaseCollection;

    public function __construct($items = [])
    {
        parent::__construct([]);

        $items = $this->getArrayableItems($items);

        foreach ($items as $key => $item) {
            $this->offsetSet($key, $item);
        }
    }

    public function offsetSet($key, $value)
    {
        $state = State::resolve($value);

        parent::offsetSet($state->stateKey(), $state);
    }

    public function available(Subject $subject, array $payload = []): self
    {
        return $this->filter(fn (StateContract $state) => $state->hasSameStateAs($subject, $payload));
    }

    public function canApply(Subject $subject, array $payload = []): bool
    {
        return $this->available($subject, $payload)->isNotEmpty();
    }
}
