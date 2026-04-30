<?php

use Tooling\PhpStan;
use Tooling\Pint;
use Tooling\Rector;

$phpStanConfigPath = realpath(__DIR__.'/../phpstan.neon');

return [
    'phpstan' => [
        'cli' => [
            PhpStan\Console\Inspectors\Analyze::class => [
                'arguments' => [
                    'paths' => when(
                        env('PHPSTAN_PATHS'),
                        fn (string $paths) => explode(',', $paths),
                        []
                    ),
                ],
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
            PhpStan\Console\Inspectors\CacheClear::class => [
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
            PhpStan\Console\Inspectors\ParametersDump::class => [
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
            PhpStan\Console\Inspectors\Diagnose::class => [
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
            PhpStan\Console\Inspectors\Bisect::class => [
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
        ],
    ],
    'rector' => [
        'cli' => [
            Rector\Console\Inspectors\Process::class => [
                'arguments' => [
                    'source' => when(
                        env('RECTOR_PATHS'),
                        fn (string $paths) => explode(',', $paths),
                        []
                    ),
                ],
                'options' => [
                    'config' => realpath(__DIR__.'/../rector.php'),
                ],
            ],
        ],
    ],
    'pint' => [
        'cli' => [
            Pint\Console\Inspector::class => [
                'arguments' => [
                    'path' => when(
                        env('PINT_PATHS'),
                        fn (string $paths) => explode(',', $paths),
                        []
                    ),
                ],
                'options' => [
                    'config' => realpath(__DIR__.'/../pint.json'),
                ],
            ],
        ],
    ],
];
