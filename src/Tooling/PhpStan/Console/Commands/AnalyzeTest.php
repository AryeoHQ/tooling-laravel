<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\PhpStan;

class AnalyzeTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = PhpStan\Console\Commands\Analyze::class;

    public string $binary = 'phpstan';

    public null|string $subcommand = 'analyse';

    public string $inspector = PhpStan\Console\Inspectors\Analyze::class;

    #[Test]
    public function it_does_not_call_cache_clear_without_the_option(): void
    {
        Process::fake();

        $this->artisan($this->command)->assertSuccessful();

        Process::assertRanTimes(fn () => true, times: 1);
    }

    #[Test]
    public function it_calls_cache_clear_when_cache_clear_option_is_passed(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--cache-clear' => true])
            ->assertSuccessful();

        Process::assertRanTimes(fn () => true, times: 2);
    }

    #[Test]
    public function it_calls_cache_clear_when_flush_option_is_passed(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--flush' => true])
            ->assertSuccessful();

        Process::assertRanTimes(fn () => true, times: 2);
    }

    #[Test]
    public function it_does_not_forward_cache_clear_to_phpstan(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--cache-clear' => true]);

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return str_contains($cmd, 'analyse') && ! str_contains($cmd, '--cache-clear');
        });
    }

    #[Test]
    public function it_does_not_forward_flush_to_phpstan(): void
    {
        Process::fake();

        $this->artisan($this->command, ['--flush' => true]);

        Process::assertRan(function ($process) {
            $cmd = implode(' ', $process->command);

            return str_contains($cmd, 'analyse') && ! str_contains($cmd, '--flush');
        });
    }
}
