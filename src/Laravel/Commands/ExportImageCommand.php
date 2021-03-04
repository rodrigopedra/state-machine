<?php

namespace RodrigoPedra\StateMachine\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Str;
use RodrigoPedra\StateMachine\Laravel\StateMachineManager;
use RodrigoPedra\StateMachine\Support\DotImageExporter;

final class ExportImageCommand extends Command
{
    protected $signature = 'state-machine:export
        {machine : A state machine}
        {--format=png}
        {--layout=TB}
        {--shape=circle}
        {--disk=local}';

    protected $description = 'Export a state machine to an image';

    public function handle(StateMachineManager $manager, Factory $filesystem, Repository $config): int
    {
        $machine = $this->argument('machine');
        $format = $this->option('format');
        $layout = $this->option('layout') === 'TB' ? 'TB' : 'LR';
        $shape = $this->option('shape');
        $disk = $this->option('disk');

        $stateMachine = $manager->driver($machine);

        $dotBinary = $config->get('state-machines.dot', 'dot');
        $exporter = new DotImageExporter($dotBinary, $format);

        $filename = Str::kebab($stateMachine->key());
        $filepath = $exporter->export(
            $stateMachine->toDot($layout, $shape),
            fn (string $format) => $filesystem->disk($disk)->path($filename . '.' . $format)
        );

        $this->info('exported to: ' . $filepath);

        return 0;
    }
}
