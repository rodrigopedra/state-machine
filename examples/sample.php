<?php

use Psr\EventDispatcher\EventDispatcherInterface;
use RodrigoPedra\StateMachine\Contracts\Subject;
use RodrigoPedra\StateMachine\StateMachine;
use RodrigoPedra\StateMachine\Support\DotImageExporter;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

class EchoDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): object
    {
        /** @var \RodrigoPedra\StateMachine\Contracts\TransitionEvent $event */
        echo \PHP_EOL;
        echo 'event: ', \basename(\str_replace('\\', '/', \get_class($event))), \PHP_EOL;

        $payload = \array_filter($event->toArray(), fn ($key) => ! \in_array($key, [
            'subject',
            'payload',
        ]), \ARRAY_FILTER_USE_KEY);

        foreach ($payload as $key => $value) {
            echo $key, ': ', $value, \PHP_EOL;
        }

        echo 'available-transitions: ', $event->stateMachine()->transitionsAvailable()->keys()->implode(', '), \PHP_EOL;

        return $event;
    }
}

$stateMachine = StateMachine::fromState('sample', 'draft', new EchoDispatcher());

$stateMachine->addState('draft');
$stateMachine->addState('approved');
$stateMachine->addState('rejected');
$stateMachine->addState('published');
$stateMachine->addState('archived');

// loop transition
$stateMachine->addTransition('review')
    ->withSources(['draft']);

$stateMachine->addTransition('approve')
    ->withSources(['draft'])
    ->withTarget('approved');

$stateMachine->addTransition('reject')
    ->withGuard(fn () => false)
    ->withSources(['draft'])
    ->withTarget('rejected');

$stateMachine->addTransition('publish')
    ->withSources(['approved'])
    ->withTarget('published');

$stateMachine->addTransition('archive')
    ->withCallback(function (Subject $subject) {
        echo \PHP_EOL, '###ARCHIVED### ', $subject->state()->stateKey(), \PHP_EOL;
    })
    ->withSources(['approved', 'rejected', 'published'])
    ->withTarget('archived');

echo 'initial-state: ', $stateMachine->currentState()->stateKey(), \PHP_EOL;
echo 'available-transitions: ', $stateMachine->transitionsAvailable()->keys()->implode(', '), \PHP_EOL;

$stateMachine->apply('add-note');
$stateMachine->apply('approve');
$stateMachine->apply('publish');
$stateMachine->applySilently('reject');
$stateMachine->apply('archive');

echo \PHP_EOL;
echo 'final-state: ', $stateMachine->currentState()->stateKey(), \PHP_EOL;

if (\is_executable('/usr/bin/dot') && \class_exists(Process::class)) {
    $exporter = new DotImageExporter('/usr/bin/dot');
    $filename = $exporter->export($stateMachine->toDot(), fn ($format) => __DIR__ . '/machine.' . $format);
    echo \PHP_EOL, 'Exported to: ', \realpath($filename);
} else {
    echo \PHP_EOL, 'Failed to export state machine graph.';
}
echo \PHP_EOL, 'Dot:', \PHP_EOL, $stateMachine->toDot(), \PHP_EOL;

echo \PHP_EOL, 'Array:', \PHP_EOL;
\var_dump($stateMachine->toArray());

echo \PHP_EOL, 'JSON:', \PHP_EOL, \json_encode($stateMachine, \JSON_PRETTY_PRINT), \PHP_EOL;
