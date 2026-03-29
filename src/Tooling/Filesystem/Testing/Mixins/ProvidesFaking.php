<?php

declare(strict_types=1);

namespace Tooling\Filesystem\Testing\Mixins;

use Closure;
use Tooling\Filesystem\Testing\FilesystemFake;

/**
 * @mixin \Illuminate\Filesystem\Filesystem
 */
class ProvidesFaking
{
    public function fake(): Closure
    {
        return function (string|array $paths = []): FilesystemFake {
            if (app()->bound(FilesystemFake::class) && app()->make(FilesystemFake::class) instanceof FilesystemFake) {
                return app()->make(FilesystemFake::class)->addFakedPaths($paths);
            }

            return tap(new FilesystemFake($paths), function (FilesystemFake $fake) {
                app()->instance(FilesystemFake::class, $fake);
                app()->instance(\Illuminate\Filesystem\Filesystem::class, $fake);
                app()->instance('files', $fake);
                \Illuminate\Support\Facades\File::swap($fake);
            });
        };
    }
}
