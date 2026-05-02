<?php

use Tooling\PhpStan;
use Tooling\Pint;
use Tooling\Rector;

$phpStanConfigPath = realpath(__DIR__.'/../phpstan.neon');
$phpStanScanPaths = array_filter(explode(',', env('PHPSTAN_PATHS', '')));

return [
    'phpstan' => [
        'cli' => [
            PhpStan\Console\Inspectors\Analyze::class => [
                'arguments' => [
                    'paths' => $phpStanScanPaths,
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
                'arguments' => [
                    'paths' => $phpStanScanPaths,
                ],
                'options' => [
                    'configuration' => $phpStanConfigPath,
                ],
            ],
        ],
    ],
    'rector' => [
        'cli' => [
            Rector\Console\Inspector::class => [
                'arguments' => [
                    'source' => array_filter(explode(',', env('RECTOR_PATHS', ''))),
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
                    'path' => array_filter(explode(',', env('PINT_PATHS', ''))),
                ],
                'options' => [
                    'config' => realpath(__DIR__.'/../pint.json'),
                ],
            ],
        ],
    ],
];
