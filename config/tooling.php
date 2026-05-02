<?php

use Tooling\PhpStan;
use Tooling\Pint;
use Tooling\Rector;

$phpStanConfigPath = realpath(__DIR__.'/../phpstan.neon');
$phpStanScanPaths = array_filter(explode(',', env('PHPSTAN_PATHS', '')));
$rectorConfigPath = realpath(__DIR__.'/../rector.php');
$rectorScanPaths = array_filter(explode(',', env('RECTOR_PATHS', '')));
$pintConfigPath = realpath(__DIR__.'/../pint.json');
$pintScanPaths = array_filter(explode(',', env('PINT_PATHS', '')));

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
            Rector\Console\Inspectors\Process::class => [
                'arguments' => [
                    'source' => $rectorScanPaths,
                ],
                'options' => [
                    'config' => $rectorConfigPath,
                ],
            ],
            Rector\Console\Inspectors\RulesList::class => [
                'options' => [
                    'config' => $rectorConfigPath,
                ],
            ],
        ],
    ],
    'pint' => [
        'cli' => [
            Pint\Console\Inspector::class => [
                'arguments' => [
                    'path' => $pintScanPaths,
                ],
                'options' => [
                    'config' => $pintConfigPath,
                ],
            ],
        ],
    ],
];
