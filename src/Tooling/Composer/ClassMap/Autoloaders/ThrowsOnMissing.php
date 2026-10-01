<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Autoloaders;

use Tooling\Composer\ClassMap\Autoloaders\Exceptions\ClassNotFound;

/**
 * Last autoloader in the chain while the class map cache builds. A missing parent, interface or
 * trait is normally a fatal error that can't be caught. Throwing here first turns it into an
 * exception, so the build can skip that class. Same trick as Symfony's
 * `ClassExistenceResource::throwOnRequiredClass`.
 */
class ThrowsOnMissing
{
    /** Functions that just ask "does this exist?". They should still get `false`, not an exception. */
    private const array EXISTENCE_CHECKS = [
        'class_exists',
        'interface_exists',
        'trait_exists',
        'enum_exists',
        'is_a',
        'is_subclass_of',
        'class_implements',
        'class_parents',
        'get_class_methods',
        'get_class_vars',
        'get_parent_class',
        'method_exists',
        'property_exists',
        'is_callable',
        'defined',
    ];

    /**
     * Only the direct caller is checked. When `is_a()` loads a file that uses a missing trait,
     * the direct caller is the file load, so this still throws like it should.
     *
     * @throws \Tooling\Composer\ClassMap\Autoloaders\Exceptions\ClassNotFound
     */
    public function __invoke(string $class): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? null;

        throw_unless(in_array($caller, self::EXISTENCE_CHECKS, true), ClassNotFound::class, $class);
    }
}
