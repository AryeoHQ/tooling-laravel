<?php

use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;
use Tooling\Rector\Rules\AddInterfaceByTrait;
use Tooling\Rector\Rules\AddTraitByInterface;

return [
    AddTraitByInterface::class => [
        GeneratesFile::class => GeneratorCommandCompatibility::class,
        TestsReference::class => ReferenceTestCases::class,
    ],
    AddInterfaceByTrait::class => [
        GeneratorCommandCompatibility::class => GeneratesFile::class,
        CreatesColocatedTests::class => GeneratesFile::class,
        ReferenceTestCases::class => TestsReference::class,
    ],
];
