<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Concerns;

use Illuminate\Support\Stringable;
use Tooling\Composer\Composer;

trait ResolvesPaths
{
    /**
     * @return array{prefix: string, path: string}
     */
    private function resolvePsr4(): array
    {
        $composer = resolve(Composer::class);
        $namespaceForMatching = $this->baseNamespace->after('\\')->finish('\\')->toString();

        $psr4 = collect((array) data_get($composer->currentAsPackage->autoload, 'psr-4', []))
            ->merge((array) data_get($composer->currentAsPackage->autoloadDev, 'psr-4', []))
            ->flatMap(fn (string|array $paths, string $prefix): array => collect((array) $paths)
                ->map(fn (string $path) => ['prefix' => $prefix, 'path' => $path])
                ->all()
            );

        $matchedPrefix = null;
        $matchedPath = null;

        foreach ($psr4 as ['prefix' => $prefix, 'path' => $basePath]) {
            if (str_starts_with($namespaceForMatching, $prefix)
                && ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix))) {
                $matchedPrefix = $prefix;
                $matchedPath = $basePath;
            }
        }

        if ($matchedPrefix === null || $matchedPath === null) {
            throw new \RuntimeException(
                "Namespace \"{$namespaceForMatching}\" does not match any PSR-4 prefix in composer.json."
            );
        }

        return ['prefix' => $matchedPrefix, 'path' => $matchedPath];
    }

    private Stringable $sourceDirectory {
        get => str($this->resolvePsr4()['path'])->rtrim('/');
    }

    private Stringable $relativeDirectory {
        get {
            $prefix = $this->resolvePsr4()['prefix'];
            $canonical = $this->namespace->after('\\')->finish('\\')->toString();

            return str(substr($canonical, strlen($prefix)))->rtrim('\\')->replace('\\', '/');
        }
    }

    public Stringable $directory {
        get {
            $projectRoot = resolve(Composer::class)->baseDirectory;
            $relative = $this->relativeDirectory;

            $source = $relative->isNotEmpty()
                ? $this->sourceDirectory->append('/', $relative->toString())
                : $this->sourceDirectory;

            return $projectRoot->append('/', $source->toString());
        }
    }

    public Stringable $filePath {
        get => $this->directory->append('/', $this->name->toString(), '.php');
    }
}
