<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Manifest;

class ToolingDiscoverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableConfigClearCommand();
        $this->disablePackageDiscoverCommand();
    }

    #[Test]
    public function it_outputs_discovering_tooling_header(): void
    {
        Manifest::fake();

        $this->artisan(ToolingDiscover::class)
            ->assertSuccessful()
            ->expectsOutputToContain('Discovering tooling');
    }

    #[Test]
    public function it_displays_loaded_tools_as_tasks(): void
    {
        Manifest::fake();

        $this->artisan(ToolingDiscover::class)
            ->assertSuccessful()
            ->expectsOutputToContain('rector')
            ->expectsOutputToContain('phpstan');
    }

    #[Test]
    public function it_creates_the_manifest_file(): void
    {
        $manifest = Manifest::fake();

        $this->artisan(ToolingDiscover::class)->assertSuccessful();

        $this->assertTrue(File::exists($manifest->manifestPath));

        tap(File::getRequire($manifest->manifestPath), function (array $result) {
            $this->assertIsArray($result['rector']);
            $this->assertIsArray($result['phpstan']);
        });
    }

    private function disableConfigClearCommand(): void
    {
        app()->instance(
            ConfigClearCommand::class,
            new class(resolve(\Illuminate\Filesystem\Filesystem::class)) extends ConfigClearCommand
            {
                public function handle(): void {}
            }
        );
    }

    private function disablePackageDiscoverCommand(): void
    {
        app()->instance(
            PackageDiscoverCommand::class,
            new class extends PackageDiscoverCommand
            {
                public function handle(null|PackageManifest $manifest = null): void {}
            }
        );
    }
}
