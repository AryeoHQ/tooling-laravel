<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;

class Untested implements Collector
{
    use Fakeable;

    public function collects(ReflectionClass $class, array $reflections): bool
    {
        $file = str((string) $class->getFileName())->beforeLast('.php');

        return ! str($class->getName())->endsWith(['Test', 'TestCases'])
            && ! File::isFile($file.'Test.php')
            && ! ($class->isTrait() && File::isFile($file.'TestCases.php'));
    }
}
