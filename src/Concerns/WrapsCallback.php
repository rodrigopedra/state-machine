<?php

namespace RodrigoPedra\StateMachine\Concerns;

use Illuminate\Contracts\Container\Container;

trait WrapsCallback
{
    protected ?Container $container;

    /** @var mixed */
    protected $callback = null;

    protected function ensureCallback($callback)
    {
        if (\is_callable($callback)) {
            return $callback;
        }

        if (\is_null($callback) || \is_null($this->container)) {
            throw new \InvalidArgumentException('Callback should be callable');
        }

        if (\is_string($callback) && \strpos($callback, '@') !== false) {
            $callback = \explode('@', $callback);
        }

        $isResolvable = \is_array($callback)
            && \count($callback) === 2
            && \is_string($callback[0])
            && \method_exists($callback[0], $callback[1]);

        if (! $isResolvable) {
            throw new \InvalidArgumentException('Callback should be callable');
        }

        return \implode('@', $callback);
    }

    public function __toString(): ?string
    {
        if (\is_null($this->callback)) {
            return null;
        }

        if (\is_object($this->callback)) {
            return \class_basename($this->callback);
        }

        if (\is_array($this->callback)) {
            return \vsprintf('%s::%s', [\class_basename($this->callback[0]), $this->callback[1]]);
        }

        if (\is_string($this->callback)) {
            return $this->callback . '()';
        }

        return \strval($this->callback);
    }

}
