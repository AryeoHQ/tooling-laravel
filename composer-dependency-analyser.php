<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreErrorsOnPackage('shipmonk/composer-dependency-analyser', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('larastan/larastan', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('canvural/larastan-strict-rules', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('ergebnis/phpstan-rules', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('phpstan/phpstan-deprecation-rules', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('phpstan/phpstan-phpunit', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('phpstan/phpstan-strict-rules', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('laravel/pint', [ErrorType::UNUSED_DEPENDENCY]);
