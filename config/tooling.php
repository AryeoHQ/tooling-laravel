<?php

return [
    'phpstan' => [
        'cli' => [
            'arguments' => [
                'paths' => when(
                    env('PHPSTAN_PATHS', null),
                    fn (string $paths) => explode(',', $paths),
                    []
                ),
            ],
            'options' => [
                'configuration' => realpath(__DIR__.'/../phpstan.neon'),
            ],
        ],
    ],
    'rector' => [
        'cli' => [
            'arguments' => [
                'source' => when(
                    env('RECTOR_PATHS', null),
                    fn (string $paths) => explode(',', $paths),
                    []
                ),
            ],
            'options' => [
                'config' => realpath(__DIR__.'/../rector.php'),
            ],
        ],
    ],
    'pint' => [
        'cli' => [
            'arguments' => [
                'path' => when(
                    env('PINT_PATHS', null),
                    fn (string $paths) => explode(',', $paths),
                    []
                ),
            ],
            'options' => [
                'config' => realpath(__DIR__.'/../pint.json'),
            ],
        ],
    ],
];
