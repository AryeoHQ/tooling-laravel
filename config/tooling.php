<?php

use Tooling\Composer\Composer;
use Tooling\PhpStan;
use Tooling\Pint;
use Tooling\Rector;

$tooling = data_get(resolve(Composer::class)->currentAsPackage, 'extra.tooling');

$phpStanConfigPath = realpath(__DIR__.'/../phpstan.neon');
$phpStanScanPaths = data_get($tooling, 'phpstan.config.paths', []);
$rectorConfigPath = realpath(__DIR__.'/../rector.php');
$rectorScanPaths = data_get($tooling, 'rector.config.paths', []);
$pintConfigPath = realpath(__DIR__.'/../pint.json');
$pintScanPaths = data_get($tooling, 'pint.config.paths', []);

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
