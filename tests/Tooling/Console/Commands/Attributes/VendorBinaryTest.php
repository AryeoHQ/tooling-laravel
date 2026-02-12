<?php

declare(strict_types=1);

namespace Tests\Tooling\Console\Commands\Attributes;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Inspectors\Inspector;
use Tooling\Pint;

class VendorBinaryTest extends TestCase
{
    #[Test]
    public function it_resolves_the_binary_name(): void
    {
        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertSame('pint', $attribute->binary);
    }

    #[Test]
    public function it_resolves_the_command(): void
    {
        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        $this->assertSame('analyse', $attribute->command);
    }

    #[Test]
    public function it_has_null_command_when_not_provided(): void
    {
        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertNull($attribute->command);
    }

    #[Test]
    public function it_resolves_the_inspector(): void
    {
        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $this->assertInstanceOf(Inspector::class, $attribute->inspector);
        $this->assertInstanceOf(Pint\Console\Inspector::class, $attribute->inspector);
    }

    #[Test]
    public function it_builds_the_correct_command_with_no_subcommand(): void
    {
        Process::fake();

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint']);
        $options = collect();

        $attribute->run($arguments, $options);

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

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        $arguments = collect(['tooling:phpstan']);
        $options = collect();

        $attribute->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('analyse', $command, true);
        });
    }

    #[Test]
    public function it_forwards_options_to_the_process(): void
    {
        Process::fake();

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint']);
        $options = collect(['--dirty']);

        $attribute->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('--dirty', $command, true);
        });
    }

    #[Test]
    public function it_forwards_extra_arguments_to_the_process(): void
    {
        Process::fake();

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $arguments = collect(['tooling:pint', 'src/']);
        $options = collect();

        $attribute->run($arguments, $options);

        Process::assertRan(function ($process) {
            $command = $process->command;

            return is_array($command) && in_array('src/', $command, true);
        });
    }

    #[Test]
    public function it_strips_inspector_aliases_from_arguments(): void
    {
        Process::fake();

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        // Force a known alias onto the inspector so the test is never skipped.
        $attribute->inspector->aliases = collect(['lint']);

        $arguments = collect(['tooling:pint', 'lint', 'src/']);
        $options = collect();

        $attribute->run($arguments, $options);

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

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'phpstan',
            command: 'analyse',
        );

        // Simulate the command name appearing in arguments (as Artisan would pass it)
        $arguments = collect(['tooling:phpstan', 'analyse', 'src/']);
        $options = collect();

        $attribute->run($arguments, $options);

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

        $attribute = new VendorBinary(
            inspector: Pint\Console\Inspector::class,
            binary: 'pint',
        );

        $baseDirectory = app(Composer::class)->baseDirectory->toString();

        $attribute->run(collect(['tooling:pint']), collect());

        Process::assertRan(function ($process) use ($baseDirectory) {
            return $process->path === $baseDirectory;
        });
    }
}
