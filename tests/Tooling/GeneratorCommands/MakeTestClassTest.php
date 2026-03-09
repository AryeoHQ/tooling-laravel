<?php

declare(strict_types=1);

namespace Tests\Tooling\GeneratorCommands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\GeneratorCommands\MakeTestClass;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;
use Tooling\GeneratorCommands\Testing\Concerns\CleansUpGeneratorCommands;

#[CoversClass(MakeTestClass::class)]
class MakeTestClassTest extends TestCase
{
    use CleansUpGeneratorCommands;

    private string $testFqcn = 'App\\Services\\Billing\\Invoice';

    public Reference $reference {
        get => (new GenericClass($this->testFqcn))->test;
    }

    /** @var array<string, mixed> */
    public array $baselineInput {
        get => ['class' => $this->testFqcn];
    }

    #[Test]
    public function it_generates_a_file_with_the_correct_namespace(): void
    {
        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = file_get_contents($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'namespace '.$this->reference->namespace.';',
            $contents,
        );
    }

    #[Test]
    public function it_generates_a_co_located_test(): void
    {
        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $this->assertFileExists($this->reference->filePath->toString());
    }

    #[Test]
    public function the_generated_test_imports_the_class_under_test(): void
    {
        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = file_get_contents($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'use '.$this->testFqcn.';',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_has_the_covers_class_attribute(): void
    {
        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = file_get_contents($this->reference->filePath->toString());

        $this->assertStringContainsString(
            '#[CoversClass(Invoice::class)]',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_extends_test_case(): void
    {
        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = file_get_contents($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'final class InvoiceTest extends TestCase',
            $contents,
        );
    }
}
