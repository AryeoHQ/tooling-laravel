<?php

declare(strict_types=1);

namespace Tests\Tooling\GeneratorCommands;

use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\GeneratorCommands\MakeTestClass;

#[CoversClass(MakeTestClass::class)]
class MakeTestClassTest extends TestCase
{
    use InteractsWithPublishedFiles; // @phpstan-ignore-line

    private string $testFqcn = 'App\\Services\\Billing\\Invoice';

    private string $expectedNamespace = 'App\\Services\\Billing';

    private string $expectedClassName = 'Invoice';

    /** @var array<array-key, string> */
    protected array $files {
        get => [
            app_path('Services/Billing/*'),
        ];
    }

    #[Test]
    public function it_generates_a_co_located_test(): void
    {
        $this->artisan(MakeTestClass::class, ['class' => $this->testFqcn])
            ->assertSuccessful();

        $this->assertFileExists(app_path('Services/Billing/InvoiceTest.php'));
    }

    #[Test]
    public function the_generated_test_has_the_correct_namespace(): void
    {
        $this->artisan(MakeTestClass::class, ['class' => $this->testFqcn])
            ->assertSuccessful();

        $contents = file_get_contents(app_path('Services/Billing/InvoiceTest.php'));

        $this->assertStringContainsString(
            'namespace '.$this->expectedNamespace.';',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_imports_the_class_under_test(): void
    {
        $this->artisan(MakeTestClass::class, ['class' => $this->testFqcn])
            ->assertSuccessful();

        $contents = file_get_contents(app_path('Services/Billing/InvoiceTest.php'));

        $this->assertStringContainsString(
            'use '.$this->testFqcn.';',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_has_the_covers_class_attribute(): void
    {
        $this->artisan(MakeTestClass::class, ['class' => $this->testFqcn])
            ->assertSuccessful();

        $contents = file_get_contents(app_path('Services/Billing/InvoiceTest.php'));

        $this->assertStringContainsString(
            '#[CoversClass('.$this->expectedClassName.'::class)]',
            $contents,
        );
    }

    #[Test]
    public function the_generated_test_extends_test_case(): void
    {
        $this->artisan(MakeTestClass::class, ['class' => $this->testFqcn])
            ->assertSuccessful();

        $contents = file_get_contents(app_path('Services/Billing/InvoiceTest.php'));

        $this->assertStringContainsString(
            'final class '.$this->expectedClassName.'Test extends TestCase',
            $contents,
        );
    }
}
