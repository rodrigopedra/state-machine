<?php

namespace RodrigoPedra\StateMachine\Collections;

use Illuminate\Support\Collection;
use RodrigoPedra\StateMachine\Concerns\ForwardsToBaseCollection;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Exceptions\UnresolvableTransitionException;
use RodrigoPedra\StateMachine\Model\Transition;

final class TransitionsCollection extends Collection
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
        if (! $value instanceof Transition) {
            throw new UnresolvableTransitionException($value);
        }

        parent::offsetSet($value->transitionKey(), $value);
    }

    public function available(Subject $subject, array $payload = []): self
    {
        return $this
            ->filter(fn (Transition $transition) => $transition->sources()->canApply($subject, $payload))
            ->filter(fn (Transition $transition) => $transition->canApply($subject, $payload));
    }

    public function canApply(Subject $subject, array $payload = []): bool
    {
        return $this->available($subject, $payload)->isNotEmpty();
    }

    public function findOrFail($transition): Transition
    {
        if ($transition instanceof Transition) {
            $transition = $this->get($transition->transitionKey());
        } else {
            $transition = $this->get($transition);
        }

        if (\is_null($transition)) {
            throw new UnresolvableTransitionException($transition);
        }

        return $transition;
    }
}
