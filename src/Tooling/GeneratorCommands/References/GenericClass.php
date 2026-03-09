<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;

final class GenericClass implements Reference
{
    public Stringable $fqcn;

    public function __construct(Stringable|string $fqcn)
    {
        $this->fqcn = str($fqcn);
    }

    public Stringable $name {
        get => str(class_basename($this->fqcn->toString()));
    }

    public null|Stringable $subdirectory = null;

    public Stringable $namespace {
        get => $this->fqcn->beforeLast('\\');
    }

    private Stringable $baseNamespace {
        get {
            $composer = resolve(Composer::class);
            $namespace = $this->fqcn->beforeLast('\\')->append('\\')->toString();

            $matchedPrefix = null;

            $psr4 = collect((array) data_get($composer->currentAsPackage->autoload, 'psr-4', []))
                ->merge((array) data_get($composer->currentAsPackage->autoloadDev, 'psr-4', []));

            foreach ($psr4 as $prefix => $basePath) {
                $prefix = (string) $prefix;

                if (str_starts_with($namespace, $prefix) && ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix))) {
                    $matchedPrefix = $prefix;
                }
            }

            if ($matchedPrefix === null) {
                throw new \RuntimeException(sprintf(
                    'Namespace "%s" does not match any PSR-4 prefix defined in composer.json.',
                    $namespace
                ));
            }

            return str($matchedPrefix)->rtrim('\\');
        }
    }

    public Stringable $baseDirectory {
        get {
            $composer = resolve(Composer::class);
            $key = $this->baseNamespace->finish('\\')->toString();

            $psr4 = (array) data_get($composer->currentAsPackage->autoload, 'psr-4', []);
            $psr4Dev = (array) data_get($composer->currentAsPackage->autoloadDev, 'psr-4', []);

            return str($psr4[$key] ?? $psr4Dev[$key])->rtrim('/');
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
