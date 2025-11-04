<?php

use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\ReplaceTestFunctionPrefixWithAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;

return [
    CoversAnnotationWithValueToAttributeRector::class,
    DataProviderAnnotationToAttributeRector::class,
    DependsAnnotationWithValueToAttributeRector::class,
    ReplaceTestFunctionPrefixWithAttributeRector::class,
    TicketAnnotationToAttributeRector::class,
];
