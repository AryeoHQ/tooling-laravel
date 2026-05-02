<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\Rector;

class ProcessTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = Rector\Console\Commands\Process::class;

    public string $binary = 'rector';

    public null|string $subcommand = 'process';

    public string $inspector = Rector\Console\Inspectors\Process::class;

    #[Test]
    public function it_forwards_clear_cache_when_cache_clear_option_is_passed(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--cache-clear' => true])->assertSuccessful();

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return str_contains($cmd, '--clear-cache');
        });
    }

    #[Test]
    public function it_forwards_clear_cache_when_flush_option_is_passed(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--flush' => true])->assertSuccessful();

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return str_contains($cmd, '--clear-cache');
        });
    }

    #[Test]
    public function it_does_not_forward_clear_cache_without_the_option(): void
    {
        Process::fake();

        $this->artisan($this->command)->assertSuccessful();

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return ! str_contains($cmd, '--clear-cache');
        });
    }

    #[Test]
    public function it_does_not_forward_cache_clear_to_rector(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--cache-clear' => true]);

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return ! str_contains($cmd, '--cache-clear');
        });
    }

    #[Test]
    public function it_does_not_forward_flush_to_rector(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--flush' => true]);

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return ! str_contains($cmd, '--flush');
        });
    }

    #[Test]
    public function it_forwards_dry_run_when_test_option_is_passed(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--test' => true])->assertSuccessful();

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return str_contains($cmd, '--dry-run');
        });
    }

    #[Test]
    public function it_does_not_forward_test_to_rector(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--test' => true]);

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return ! str_contains($cmd, '--test');
        });
    }
}
