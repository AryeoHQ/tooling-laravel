<?php

declare(strict_types=1);

namespace Tooling\Composer\Testing;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMapSource;
use Tooling\Composer\Composer;
use Tooling\Composer\Manifest;

final class ComposerFake extends Composer
{
    public Stringable $vendorDirectory { get => str('/fake/vendor'); }

    public Stringable $baseDirectory { get => str('/fake'); }

    /**
     * @param  array<string, mixed>  $composerJson
     */
    public static function make(array $composerJson = []): static
    {
        $files = File::fake('/fake/*');
        $classMapSource = ClassMapSourceFake::make();

        $fake = new self(files: $files, classMapSource: $classMapSource);

        app()->instance(Composer::class, $fake);
        app()->instance(ClassMapSource::class, $classMapSource);
        app()->instance(Manifest::class, ManifestFake::make());
        app()->instance(Cache::class, CacheFake::make());

        $files->put($fake->composerJsonPath, json_encode(array_replace_recursive([
            'name' => 'test/package',
            'autoload' => ['psr-4' => ['App\\' => 'src/']],
        ], $composerJson)));

        $files->put($fake->packages->installedManifestPath, json_encode(['packages' => [
            ['name' => 'test/dependency', 'version' => '1.0.0', 'description' => 'A test dependency'],
        ]]));

        $files->put($fake->vendorPath('autoload.php'), '<?php // fake autoload');

        foreach ($fake->currentAsPackage->psr4Mappings as $mapping) {
            $directory = rtrim($fake->baseDirectory->append('/'.$mapping->path->toString())->toString(), '/');
            $files->makeDirectory($directory);
            $classMapSource->merge(['App\\Example' => $directory.'/Example.php']);
        }

        // In a faked / testing scenario files are written with the same second timestamp.
        // As such it appears as if our cache is stale when in fact it's not.
        // We can advance time so that cache files written after this point
        // have a later timestamp than the source files above.
        Date::setTestNow(now()->addSecond());

        return $fake;
    }

    /**
     * @param  array<string, mixed>  $composerJson
     */
    public function merge(array $composerJson): static
    {
        File::put($this->composerJsonPath, json_encode(array_replace_recursive(
            json_decode(File::get($this->composerJsonPath), true),
            $composerJson
        )));

        return $this->clearCache();
    }

    public function clearCache(): static
    {
        $this->cache = [];

        return $this;
    }
}
