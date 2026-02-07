<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Enums\Fixtures;

/**
 * Invalid enum cases - all should fail PascalCase validation
 */
enum InvalidPascalCaseEnum: string
{
    // lowercase start
    case colonial = 'colonial';
    case capeCod = 'cape_cod';
    case aFrame = 'a_frame';

    // All uppercase
    case COLONIAL = 'colonial';
    case CAPE_COD = 'cape_cod';

    // Snake case
    case Cape_Cod = 'cape_cod';
    case cape_cod = 'cape_cod';
    case a_frame = 'a_frame';
}
