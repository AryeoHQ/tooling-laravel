<?php

declare(strict_types=1);

namespace Tooling\Console\Commands\Attributes;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Console\Inspectors\Inspector;
use Tooling\Pint;

class VendorBinaryTest extends TestCase
{
    #[Test]
    public function it_resolves_the_binary_name(): void
    {
        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertSame('pint', $vendorBinary->binary);
    }

    #[Test]
    public function it_resolves_the_command(): void
    {
        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        $this->assertSame('analyse', $vendorBinary->command);
    }

    #[Test]
    public function it_has_null_command_when_not_provided(): void
    {
        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertNull($vendorBinary->command);
    }

    #[Test]
    public function it_resolves_the_inspector(): void
    {
        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertInstanceOf(Inspector::class, $vendorBinary->inspector);
        $this->assertInstanceOf(Pint\Console\Inspector::class, $vendorBinary->inspector);
    }

    #[Test]
    public function it_builds_the_correct_command_with_no_subcommand(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint']);
        $options = collect();

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command)
                && str_contains(end($command), 'pint');
        });
    }

    #[Test]
    public function it_builds_the_correct_command_with_subcommand(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        $arguments = collect(['tooling:phpstan']);
        $options = collect();

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('analyse', $command, true);
        });
    }

    #[Test]
    public function it_forwards_options_to_the_process(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint']);
        $options = collect(['--dirty']);

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('--dirty', $command, true);
        });
    }

    #[Test]
    public function it_forwards_extra_arguments_to_the_process(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint', 'src/']);
        $options = collect();

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('src/', $command, true);
        });
    }

    #[Test]
    public function it_strips_inspector_aliases_from_arguments(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        // Force a known alias onto the inspector so the test is never skipped.
        $vendorBinary->inspector->aliases = collect(['lint']);

        $arguments = collect(['tooling:pint', 'lint', 'src/']);
        $options = collect();

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command)
                && ! in_array('lint', $command, true)
                && in_array('src/', $command, true);
        });
    }

    #[Test]
    public function it_strips_the_subcommand_from_forwarded_arguments(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        // Simulate the command name appearing in arguments (as Artisan would pass it)
        $arguments = collect(['tooling:phpstan', 'analyse', 'src/']);
        $options = collect();

        $vendorBinary->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            // 'analyse' should appear exactly once (from the VendorBinary, not duplicated from args)
            $count = count(array_filter($command, fn ($v) => $v === 'analyse'));

            return $count === 1 && in_array('src/', $command, true);
        });
    }

    #[Test]
    public function it_runs_from_the_base_directory(): void
    {
        Process::fake();

        $vendorBinary = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $baseDirectory = resolve(Composer::class)->baseDirectory->toString();

        $vendorBinary->run(collect(['tooling:pint']), collect());

        Process::assertRan(function ($process) use ($baseDirectory) {
            return $process->path === $baseDirectory;
        });
    }
}
