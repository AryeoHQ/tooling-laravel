<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands\Make\References;

use Illuminate\Support\Stringable;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\TestClass;

final class RectorRule implements Reference
{
    public Stringable $name;

    public Stringable $baseNamespace;

    public function __construct(Stringable|string $name, Stringable|string $baseNamespace)
    {
        $this->name = str($name);
        $this->baseNamespace = str($baseNamespace);
    }

    public null|Stringable $subdirectory = null;

    public Stringable $namespace {
        get => $this->baseNamespace->finish('\\')->append('Rector\\Rules');
    }

    public Stringable $fqcn {
        get => $this->namespace->append('\\', $this->name->toString());
    }

    public Stringable $baseDirectory {
        get {
            $composer = resolve(Composer::class);
            $key = $this->baseNamespace->finish('\\')->toString();

            $psr4 = (array) data_get($composer->currentAsPackage->autoload, 'psr-4', []);
            $psr4Dev = (array) data_get($composer->currentAsPackage->autoloadDev, 'psr-4', []);

            return str($psr4[$key] ?? $psr4Dev[$key] ?? 'app')->rtrim('/');
        }
    }

    public Stringable $relativeDirectory {
        get => $this->namespace->after($this->baseNamespace->toString())->replace('\\', '/');
    }

    public Stringable $directory {
        get => $this->baseDirectory->append('/', $this->relativeDirectory->toString());
    }

    public Stringable $directoryPath {
        get => resolve(Composer::class)->baseDirectory->append('/', $this->directory->toString());
    }

    public Stringable $filePath {
        get => $this->directoryPath->append('/', $this->name->toString(), '.php');
    }

    public TestClass $test {
        get => new TestClass($this);
    }
}
