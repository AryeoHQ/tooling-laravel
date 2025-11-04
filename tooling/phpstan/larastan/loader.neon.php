<?php

use Tooling\Composer\Composer;

use function Illuminate\Filesystem\join_paths;

$composer = new Composer;

$artisans = collect(['artisan', 'vendor/bin/testbench'])->map(
    fn (string $path): string => join_paths($composer->baseDirectory, $path)
)->filter(
    fn (string $path): bool => is_file($path)
);

return $artisans->isNotEmpty() ? ['includes' => [__DIR__.'/config.neon']] : [];
