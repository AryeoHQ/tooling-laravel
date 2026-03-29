<?php

declare(strict_types=1);

namespace Tooling\Composer\Testing;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Tooling\Composer\ClassMap\Cache;

final class CacheFake extends Cache
{
    public static function make(): static
    {
        return new self;
    }

    /**
     * @param  array<string, array<int, string>>  $data
     */
    public function provide(array $data): static
    {
        // In a faked / testing scenario files are written with the same second timestamp.
        // Advance time so that the cache file is strictly newer than any prior source modifications,
        // accurately modeling real-world causality where cache writes happen after source changes.
        Date::setTestNow(now()->addSecond());

        File::ensureDirectoryExists(dirname($this->cachePath));
        File::put($this->cachePath, '<?php return '.var_export($data, true).';');

        return $this;
    }
}
