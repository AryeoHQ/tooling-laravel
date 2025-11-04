<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use Illuminate\Support\Stringable;
use stdClass;

final class Package
{
    private object $data;

    public null|Stringable $name { get => $this->name ??= $this->get('name'); }

    public null|Stringable $version { get => $this->version ??= $this->get('version'); }

    public null|Stringable $description { get => $this->description ??= $this->get('description'); }

    public null|object $extra { get => $this->extra ??= $this->get('extra', new stdClass); }

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
