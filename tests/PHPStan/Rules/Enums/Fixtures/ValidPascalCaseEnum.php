<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Enums\Fixtures;

/**
 * Valid enum cases - all should pass PascalCase validation
 */
enum ValidPascalCaseEnum: string
{
    // Traditional PascalCase
    case Colonial = 'colonial';
    case CapeCod = 'cape_cod';
    case FooBar = 'foo_bar';
    case FooBarBaz = 'foo_bar_baz';

    // Single-letter "words" in PascalCase (the fix allows these)
    case AFrame = 'a_frame';
    case ABTest = 'ab_test';
    case IOStream = 'io_stream';
    case A = 'a';
    case AB = 'ab';
    case ABC = 'abc';
}
