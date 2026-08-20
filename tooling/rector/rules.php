<?php

use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Tooling\Rector\Rules\ReplaceCarbonWithDateFacade;
use Tooling\Rector\Rules\ReplaceTestFunctionPrefixWithAttribute;

return [
    CoversAnnotationWithValueToAttributeRector::class,
    DataProviderAnnotationToAttributeRector::class,
    DependsAnnotationWithValueToAttributeRector::class,
    ReplaceCarbonWithDateFacade::class,
    ReplaceTestFunctionPrefixWithAttribute::class,
    TicketAnnotationToAttributeRector::class,
];
