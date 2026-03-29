<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;

#[CoversClass(MakeTestClass::class)]
class MakeTestClassTest extends TestCase
{
    private string $testFqcn = 'App\\Services\\Billing\\Invoice';

    private string $searchFqcn = 'App\\Services\\Billing\\Payment';

    public Reference $reference {
        get => GenericClass::fromFqcn($this->testFqcn)->test;
    }

    public Reference $searchReference {
        get => GenericClass::fromFqcn($this->searchFqcn);
    }

    /** @var array<string, mixed> */
    public array $baselineInput {
        get => ['class' => $this->testFqcn];
    }

    #[Test]
    public function it_generates_a_file_with_the_correct_namespace(): void
    {
        Composer::fake();

        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = File::get($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'namespace '.$this->reference->namespace->after('\\').';',
            $contents,
        );
    }

    #[Test]
    public function it_generates_a_co_located_test(): void
    {
        Composer::fake();

        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $this->assertTrue(File::exists($this->reference->filePath->toString()));
    }

    #[Test]
    public function the_generated_test_imports_the_class_under_test(): void
    {
        Composer::fake();

        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = File::get($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'use '.$this->testFqcn.';',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_has_the_covers_class_attribute(): void
    {
        Composer::fake();

        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = File::get($this->reference->filePath->toString());

        $this->assertStringContainsString(
            '#[CoversClass(Invoice::class)]',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_extends_test_case(): void
    {
        Composer::fake();

        $this->artisan(MakeTestClass::class, $this->baselineInput)
            ->assertSuccessful();

        $contents = File::get($this->reference->filePath->toString());

        $this->assertStringContainsString(
            'final class InvoiceTest extends TestCase',
            $contents,
        );
    }

    #[Test]
    public function it_prompts_for_class_and_discovers_via_cache_when_no_argument_is_provided(): void
    {
        Composer::fake();
        $expectedResults = Untested::fake([$this->searchFqcn]);

        $searchResults = collect($expectedResults)
            ->filter(fn (string $class) => str($class)->lower()->is('*'.str($this->searchFqcn)->lower()->toString().'*'))
            ->values()
            ->toArray();

        $this->artisan(MakeTestClass::class)
            ->expectsSearch('Which class would you like to test?', $this->searchFqcn, $this->searchFqcn, $searchResults)
            ->assertSuccessful();

        $this->assertTrue(
            File::exists(
                GenericClass::fromFqcn($this->searchFqcn)->test->filePath->toString()
            )
        );
    }
}
