<?php

namespace RodrigoPedra\StateMachine\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

trait SerializesValue
{
    public function serializeValue($value, $default = null)
    {
        if (\is_callable($value)) {
            $value = \call_user_func($value);
        }

        if (\is_null($value) || \is_scalar($value) || \is_array($value)) {
            return null;
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        if ($value instanceof Jsonable) {
            return json_decode($value->toJson(), true);
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (\is_callable($default)) {
            return \call_user_func($default, $value);
        }

        return $default;
    }
}
