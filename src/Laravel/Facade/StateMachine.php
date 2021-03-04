<?php

namespace RodrigoPedra\StateMachine\Laravel\Facade;

use Illuminate\Support\Facades\Facade;
use RodrigoPedra\StateMachine\Laravel\StateMachineManager;

/**
 * @method static string key()
 * @method static string subjectKey()
 * @method static \RodrigoPedra\StateMachine\Contracts\Subject subject()
 * @method static \RodrigoPedra\StateMachine\Contracts\State currentState()
 * @method static \RodrigoPedra\StateMachine\Collections\StatesCollection states()
 * @method static \RodrigoPedra\StateMachine\Collections\TransitionsCollection transitions()
 * @method static \RodrigoPedra\StateMachine\Collections\TransitionsCollection transitionsAvailable(array $payload = [])
 * @method static \RodrigoPedra\StateMachine\StateMachine addState($state)
 * @method static \RodrigoPedra\StateMachine\Model\Transition addTransition(string $key)
 * @method static bool canApply($transition, array $payload = [])
 * @method static \RodrigoPedra\StateMachine\StateMachine apply($transition, array $payload = [])
 * @method static bool applySilently($transition, array $payload = [])
 * @method static void guardState(\RodrigoPedra\StateMachine\Contracts\State $state)
 * @method static \RodrigoPedra\StateMachine\StateMachine withContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \RodrigoPedra\StateMachine\StateMachine driver($driver = null)
 * @method static \RodrigoPedra\StateMachine\StateMachine machine(?string $machine = null)
 * @method static \RodrigoPedra\StateMachine\Laravel\StateMachineManager extend($driver, \Closure $callback)
 * @method static \RodrigoPedra\StateMachine\StateMachine withSubject(\RodrigoPedra\StateMachine\Contracts\Subject $subject)
 * @method static \RodrigoPedra\StateMachine\StateMachine subjectAs(string $subjectKey)
 * @method static array toArray()
 * @method static string toJson($options = 0)
 * @method static array jsonSerialize()
 * @method static string toDot(string $layout = 'TB', string $shape = 'circle')
 * @see \RodrigoPedra\StateMachine\StateMachine
 * @see \RodrigoPedra\StateMachine\Laravel\StateMachineManager
 */
class StateMachine extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StateMachineManager::class;
    }
}
