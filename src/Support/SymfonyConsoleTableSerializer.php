<?php

namespace RodrigoPedra\StateMachine\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use RodrigoPedra\StateMachine\Concerns\WrapsCallback;
use RodrigoPedra\StateMachine\Contracts\State;
use RodrigoPedra\StateMachine\Contracts\StateMachineSerializer;
use RodrigoPedra\StateMachine\Model\NullState;
use RodrigoPedra\StateMachine\Model\Transition;
use RodrigoPedra\StateMachine\StateMachine;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsoleTableSerializer implements StateMachineSerializer
{
    private OutputInterface $output;
    private array $listeners;

    public function __construct(OutputInterface $output, array $listeners = [])
    {
        $this->output = $output;
        $this->listeners = $listeners;
    }

    public function serialize(StateMachine $stateMachine)
    {
        $this->table(['Machine', 'Subject key'], [[$stateMachine->key(), $stateMachine->subjectKey()]], 'box');

        $states = $stateMachine->states()
            ->values()
            ->map(fn (State $state) => [
                $state->stateKey(),
                $state->hasSameStateAs($stateMachine->subject()) ? 'Yes' : '-',
            ]);

        if ($states->isEmpty()) {
            $states = [['(no states)', '-', '-']];
        }

        $this->table(['State', 'Is initial?'], $states, 'box');

        $transitions = $stateMachine->transitions()
            ->values()
            ->flatMap(function (Transition $transition, int $index) {
                if ($transition->sources()->isEmpty()) {
                    return [$this->transitionRow($transition, NullState::get(), true)];
                }

                return $transition->sources()
                    ->values()
                    ->map(fn (State $state, int $index) => $this->transitionRow($transition, $state, $index === 0))
                    ->when($index > 0, fn (Collection $rows) => $rows->prepend(new TableSeparator()));
            });

        if ($transitions->isEmpty()) {
            $transitions = [['(no transitions)', '-', '-', '-', '-', '-']];
        }

        $this->table(['Transition', 'Is Loop?', 'Guard', 'Callback', 'Source', 'Target',], $transitions, 'box');
        $sentinel = (object) ['first' => true];

        $listeners = Collection::make($this->listeners)
            ->flatMap(fn (array $listeners, string $event) => Collection::make($listeners)
                ->map(fn (array $listener, int $index) => $this->eventRow($event, $listener, $index === 0))
                ->when(
                    ! $sentinel->first,
                    fn (Collection $rows) => $rows->prepend(new TableSeparator()),
                    function (Collection $rows) use ($sentinel) {
                        $sentinel->first = false;

                        return $rows;
                    }
                )
            );

        if ($listeners->isEmpty()) {
            $listeners = [['(no event listeners)', '-', '-']];
        }

        $this->table(['Event', 'Transition', 'Handler'], $listeners, 'box');
    }

    private function transitionRow(Transition $transition, State $source, bool $isFirstRow): array
    {
        $source = NullState::isNull($source)
            ? '(any)'
            : $source->stateKey();

        $target = $transition->isLoop()
            ? $source
            : $transition->target()->stateKey();

        if ($isFirstRow) {
            return [
                $transition->transitionKey(),
                $transition->isLoop() ? 'Yes' : '-',
                \strval($transition->guard() ?? '-'),
                \strval($transition->callback() ?? '-'),
                $source,
                $target,
            ];
        }

        if ($transition->isLoop()) {
            return [null, null, null, null, $source, $target];
        }

        return [null, null, null, null, $source, null];
    }

    private function eventRow(string $event, array $listener, bool $isFirstRow): array
    {
        $transition = $listener['on'] ?? '(any)';
        $callback = $listener['handler'];

        if (\is_string($callback) && \method_exists($callback, 'handle')) {
            $callback = [$callback, 'handle'];
        }

        $listener = new class($callback) {
            use WrapsCallback;

            public function __construct($callback)
            {
                $this->callback = $callback;
            }
        };

        if ($isFirstRow) {
            return [
                \class_basename($event),
                $transition,
                \strval($listener),
            ];
        }

        return [null, $transition, \strval($listener)];
    }

    /**
     * Extracted from the illuminate/console package
     * Copyright (c) Taylor Otwell
     * Licensed under the MIT License (MIT)
     * License available in:
     * https://github.com/illuminate/console/blob/5c40583f3b70dc3eb1bfaa08aa05d1e44d6c6679/LICENSE.md
     * Code available in:
     * https://github.com/illuminate/console/blob/5c40583f3b70dc3eb1bfaa08aa05d1e44d6c6679/Concerns/InteractsWithIO.php#L215-L239
     *
     * @param $headers
     * @param $rows
     * @param  string  $tableStyle
     * @param  array  $columnStyles
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }
}
