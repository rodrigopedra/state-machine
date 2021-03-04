<?php

namespace RodrigoPedra\StateMachine\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use RodrigoPedra\StateMachine\Laravel\Builder\StateMachineBuilder;
use RodrigoPedra\StateMachine\Laravel\Commands\ExportImageCommand;
use RodrigoPedra\StateMachine\Laravel\Commands\ShowCommand;

class StateMachineServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(StateMachineManager::class);

        $this->app->afterResolving(StateMachineManager::class,
            function (StateMachineManager $manager, Container $container) {
                $config = $container->make(Repository::class);
                $stateMachines = $config->get('state-machines.machines', []);

                foreach ($stateMachines as $key => $stateMachine) {
                    $manager->extend($key, \Closure::fromCallable(new StateMachineBuilder($key, $stateMachine)));
                }
            });
    }

    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/state-machines.php' => $this->app->configPath('state-machines.php'),
        ]);

        $this->commands([
            ExportImageCommand::class,
            ShowCommand::class,
        ]);
    }
}
