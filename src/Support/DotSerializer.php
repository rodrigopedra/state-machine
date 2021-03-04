<?php

namespace RodrigoPedra\StateMachine\Support;

use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\StateMachineSerializer;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;

final class DotSerializer implements StateMachineSerializer
{
    private string $layout;
    private string $shape;

    public function __construct(string $layout = 'TB', string $shape = 'circle')
    {
        if (! \in_array($layout, ['TB', 'LR'])) {
            throw new \InvalidArgumentException(\sprintf("Invalid layout '%s'", $layout));
        }

        $this->layout = $layout;
        $this->shape = $shape;
    }

    public function serialize(StateMachine $stateMachine): string
    {
        $dot = [];
        $dot[] = 'digraph finite_state_machine {';
        $dot[] = "rankdir={$this->layout};";
        $dot[] = 'node [shape = point]; __initial__';

        $initialState = $stateMachine->initialState()->stateKey();

        $dot[] = "node [shape = {$this->shape}];";
        $dot[] = "__initial__ -> \"{$initialState}\";";

        $dot = $stateMachine->transitions()->reduce(function (array $dot, Transition $transition) {
            return $transition->sources()->reduce(function (array $dot, State $state) use ($transition) {
                $target = $transition->isLoop()
                    ? $state
                    : $transition->target();

                $dot[] = \vsprintf('"%s" -> "%s" [label = "%s"];', [
                    $state->stateKey(),
                    $target->stateKey(),
                    $transition->transitionKey(),
                ]);

                return $dot;
            }, $dot);
        }, $dot);

        $dot[] = '}';

        return implode(\PHP_EOL, $dot);
    }
}
