<?php

namespace RodrigoPedra\StateMachine\Concerns;

use Illuminate\Support\Collection;

trait ForwardsToBaseCollection
{
    public static function range($from, $to): Collection
    {
        return Collection::range($from, $to);
    }

    public function keys(): Collection
    {
        return $this->toBase()->keys();
    }

    public function values(): Collection
    {
        return $this->toBase()->values();
    }
}
