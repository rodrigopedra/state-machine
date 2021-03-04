<?php

return [
    'default' => \env('STATE_MACHINE_DEFAULT_MACHINE'),
    
    // You need to have graphviz installed and provide its
    // `dot` tool binary path to export state machines to an image
    'dot' => \env('STATE_MACHINE_DOT_BINARY', '/usr/bin/dot'),

    'machines' => [
        'sample' => [
            'initial_state' => 'draft',
            'states' => [
                'draft',
                'approved',
                'rejected',
                'published',
                'archived',
            ],
            'transitions' => [
                'add-note' => [
                    // as there is no target, this will be
                    // a loop transition
                    'callback' => 'var_dump',
                    'sources' => [
                        'draft',
                    ],
                ],
                'approve' => [
                    'sources' => [
                        'draft',
                    ],
                    'target' => 'approved',
                ],
                'reject' => [
                    'sources' => [
                        'draft',
                    ],
                    'target' => 'rejected',
                ],
                'publish' => [
                    // A guard will be used to authorize
                    // applying this transition
                    'guard' => fn () => true,
                    'sources' => [
                        'approved',
                    ],
                    'target' => 'published',
                ],
                'archive' => [
                    'sources' => [
                        'approved',
                        'rejected',
                        'published',
                    ],
                    'target' => 'archived',
                ],
            ],
            'listeners' => [
                RodrigoPedra\StateMachine\Contracts\TransitionEvent::class => [
                    [
                        // This listener will be fired
                        // to all transitions
                        'handler' => 'var_dump',
                    ],
                ],
                RodrigoPedra\StateMachine\Events\TransitionApplied::class => [
                    [
                        'on' => 'closed',
                        'handler' => 'var_dump',
                    ],
                ],
            ],
        ],
    ],
];
