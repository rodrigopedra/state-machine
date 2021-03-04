<?php

namespace RodrigoPedra\StateMachine\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use RodrigoPedra\StateMachine\Laravel\StateMachineManager;
use RodrigoPedra\StateMachine\Support\SymfonyConsoleTableSerializer;

final class ShowCommand extends Command
{
    protected $signature = 'state-machine:show {machine : A state machine}';

    protected $description = 'Visualize a state machine';

    public function handle(StateMachineManager $manager, Repository $config): int
    {
        $machine = $this->argument('machine');
        $stateMachine = $manager->driver($machine);

        $serializer = new SymfonyConsoleTableSerializer(
            $this->output,
            $config->get("state-machines.machines.{$machine}.listeners", [])
        );

        $serializer->serialize($stateMachine);

        return 0;
    }
}
