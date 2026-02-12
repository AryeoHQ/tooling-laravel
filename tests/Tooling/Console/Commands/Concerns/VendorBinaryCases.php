<?php

declare(strict_types=1);

namespace Tests\Tooling\Console\Commands\Concerns;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\Console\Inspectors\Inspector;

trait VendorBinaryCases
{
    protected VendorBinary $vendorBinary {
        get => (new ReflectionClass($this->command))
            ->getAttributes(VendorBinary::class)[0]->newInstance();
    }

    #[Test]
    public function it_uses_the_handled_by_vendor_binary_trait(): void
    {
        $this->assertContains(
            HandledByVendorBinary::class,
            class_uses_recursive($this->command),
        );
    }

    #[Test]
    public function it_has_the_vendor_binary_attribute(): void
    {
        $attributes = (new ReflectionClass($this->command))
            ->getAttributes(VendorBinary::class);

        $this->assertNotEmpty($attributes);
    }

    #[Test]
    public function it_resolves_the_correct_binary(): void
    {
        $this->assertSame($this->binary, $this->vendorBinary->binary);
    }

    #[Test]
    public function it_resolves_the_correct_subcommand(): void
    {
        $this->assertSame($this->subcommand, $this->vendorBinary->command);
    }

    #[Test]
    public function it_resolves_the_correct_inspector(): void
    {
        $this->assertInstanceOf(Inspector::class, $this->vendorBinary->inspector);
        $this->assertInstanceOf($this->inspector, $this->vendorBinary->inspector);
    }

    #[Test]
    public function it_delegates_to_process(): void
    {
        Process::fake();

        $this->artisan($this->command);

        Process::assertRan(function ($process) {
            return is_array($process->command)
                && str_contains(implode(' ', $process->command), $this->binary);
        });
    }

    #[Test]
    public function it_includes_the_subcommand_in_the_process(): void
    {
        if ($this->subcommand === null) {
            $this->assertTrue(true);

            return;
        }

        Process::fake();

        $this->artisan($this->command);

        Process::assertRan(function ($process) {
            return is_array($process->command)
                && in_array($this->subcommand, $process->command, true);
        });
    }

    #[Test]
    public function it_returns_success_on_successful_process(): void
    {
        Process::fake();

        $this->artisan($this->command)->assertSuccessful();
    }

    #[Test]
    public function it_returns_failure_on_failed_process(): void
    {
        Process::fake([
            '*' => Process::result(exitCode: 1),
        ]);

        $this->artisan($this->command)->assertFailed();
    }
}
