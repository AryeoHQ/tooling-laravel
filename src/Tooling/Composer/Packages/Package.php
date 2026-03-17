<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use stdClass;

final class Package
{
    private object $data;

    public null|Stringable $name { get => $this->name ??= $this->get('name'); }

    public null|Stringable $version { get => $this->version ??= $this->get('version'); }

    public null|Stringable $description { get => $this->description ??= $this->get('description'); }

    public null|object $extra { get => $this->extra ??= $this->get('extra', new stdClass); }

    public null|object $autoload { get => $this->autoload ??= $this->get('autoload', new stdClass); }

    public null|object $autoloadDev { get => $this->autoloadDev ??= $this->get('autoload-dev', new stdClass); }

    /** @var Collection<int, Psr4Mapping> */
    public Collection $psr4Mappings {
        get => $this->psr4Mappings ??= collect((array) data_get($this->autoload, 'psr-4', []))
            ->merge((array) data_get($this->autoloadDev, 'psr-4', []))
            ->flatMap(fn (string|array $paths, string $prefix): array => collect((array) $paths)
                ->map(fn (string $path) => new Psr4Mapping($prefix, $path))
                ->all()
            );
    }

    public function __construct(object $data)
    {
        $this->data = $data;
    }

    private function get(string $key, mixed $default = null): mixed
    {
        $result = data_get($this->data, $key, $default);

        return match (true) {
            is_string($result) => str($result),
            default => $result,
        };
    }
}
