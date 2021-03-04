<?php

namespace RodrigoPedra\StateMachine\Callback;

use Illuminate\Contracts\Container\Container;
use RodrigoPedra\StateMachine\Concerns\WrapsCallback;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\Contracts\TransitionCallback as TransitionCallbackContract;
use RodrigoPedra\StateMachine\Model\Transition;

final class TransitionCallback implements TransitionCallbackContract
{
    use WrapsCallback;

    private Transition $transition;
    private string $subjectKey;

    public function __construct(?Container $container, Transition $transition, string $subjectKey, $callback)
    {
        $this->container = $container;
        $this->transition = $transition;
        $this->subjectKey = $subjectKey;
        $this->callback = $this->ensureCallback($callback);
    }

    /**
     * @param  \RodrigoPedra\StateMachine\Contracts\Subject  $subject
     * @param  array  $payload
     * @return mixed
     */
    public function call(Subject $subject, array $payload = [])
    {
        if (\is_null($this->container)) {
            $payload = \array_values($payload);

            return \call_user_func_array($this->callback, [$subject, $this->transition, ...$payload]);
        }

        $payload[$this->subjectKey] = $subject;
        $payload['transition'] = $this->transition;

        return $this->container->call($this->callback, $payload);
    }
}
