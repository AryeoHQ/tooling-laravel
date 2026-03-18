<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Concerns;

use Illuminate\Support\Stringable;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Psr4Mapping;

trait ResolvesPaths
{
    private function resolvePsr4(): Psr4Mapping
    {
        $composer = resolve(Composer::class);
        $namespaceForMatching = $this->baseNamespace->finish('\\')->toString();

        $matched = null;

        foreach ($composer->currentAsPackage->psr4Mappings as $mapping) {
            if (str_starts_with($namespaceForMatching, $mapping->prefix->toString())
                && ($matched === null || $mapping->prefix->length() > $matched->prefix->length())) {
                $matched = $mapping;
            }
        }

        if ($matched === null) {
            throw new \RuntimeException(
                "Namespace \"{$namespaceForMatching}\" does not match any PSR-4 prefix in composer.json."
            );
        }

        return $matched;
    }

    private Stringable $sourceDirectory {
        get => $this->resolvePsr4()->path->rtrim('/');
    }

    private Stringable $relativeDirectory {
        get {
            $prefix = $this->resolvePsr4()->prefix->toString();
            $canonical = $this->namespace->finish('\\')->toString();

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
