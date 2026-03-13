<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Package;
use Tooling\GeneratorCommands\References\Concerns\ManagesNamespaceTestCases;
use Tooling\GeneratorCommands\References\Concerns\ResolvesPathsTestCases;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

#[CoversClass(GenericClass::class)]
class GenericClassTest extends TestCase implements TestsReference
{
    use ManagesNamespaceTestCases;
    use ReferenceTestCases;
    use ResolvesPathsTestCases;

    public Reference $subject {
        get => new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App\\Services\\Billing');
    }

    public string $expectedName = 'Invoice';

    #[Test]
    public function it_resolves_the_directory_via_psr4_mapping(): void
    {
        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App');

        $this->assertStringEndsWith('/workbench/app', $reference->directory->toString());
    }

    #[Test]
    public function it_appends_relative_namespace_segments_to_the_directory(): void
    {
        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App\\Services\\Billing');

        $this->assertStringEndsWith('/workbench/app/Services/Billing', $reference->directory->toString());
    }

    #[Test]
    public function it_derives_properties_from_fqcn(): void
    {
        $reference = GenericClass::fromFqcn('\\Workbench\\App\\Services\\Billing\\Invoice');

        $this->assertSame('Invoice', $reference->name->toString());
        $this->assertSame('\\Workbench\\App\\Services\\Billing', $reference->baseNamespace->toString());
        $this->assertNull($reference->subNamespace);
        $this->assertSame('\\Workbench\\App\\Services\\Billing\\Invoice', $reference->fqcn->toString());
    }

    #[Test]
    public function it_normalizes_fqcn_without_leading_backslash(): void
    {
        $reference = GenericClass::fromFqcn('Workbench\\App\\Services\\Billing\\Invoice');

        $this->assertSame('Invoice', $reference->name->toString());
        $this->assertSame('\\Workbench\\App\\Services\\Billing', $reference->baseNamespace->toString());
        $this->assertSame('\\Workbench\\App\\Services\\Billing\\Invoice', $reference->fqcn->toString());
    }

    #[Test]
    public function it_throws_when_namespace_does_not_match_any_psr4_prefix(): void
    {
        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Unknown\\Namespace');

        $this->expectException(\RuntimeException::class);

        $reference->directory; // @phpstan-ignore expr.resultUnused
    }

    #[Test]
    public function it_strips_sub_namespace_from_fqcn(): void
    {
        $class = new class('Invoice', 'Workbench\\App') extends GenericClass
        {
            public null|Stringable $subNamespace {
                get => str('Services\\Billing');
            }
        };

        $reference = $class::fromFqcn('\\Workbench\\App\\Services\\Billing\\Invoice');

        $this->assertSame('Invoice', $reference->name->toString());
        $this->assertSame('\\Workbench\\App', $reference->baseNamespace->toString());
        $this->assertSame('Services\\Billing', $reference->subNamespace->toString());
        $this->assertSame('\\Workbench\\App\\Services\\Billing\\Invoice', $reference->fqcn->toString());
    }

    #[Test]
    public function it_throws_when_fqcn_does_not_contain_expected_sub_namespace(): void
    {
        $class = new class('Invoice', 'Workbench\\App') extends GenericClass
        {
            public null|Stringable $subNamespace {
                get => str('Services\\Billing');
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        $class::fromFqcn('\\Workbench\\App\\Models\\Invoice');
    }

    #[Test]
    public function it_throws_when_fqcn_namespace_does_not_end_with_sub_namespace(): void
    {
        $class = new class('Invoice', 'Workbench\\App') extends GenericClass
        {
            public null|Stringable $subNamespace {
                get => str('Services\\Billing');
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        $class::fromFqcn('\\Workbench\\App\\Services\\Billing\\Extra\\Invoice');
    }

    #[Test]
    public function it_resolves_psr4_when_path_is_an_array(): void
    {
        $composer = resolve(Composer::class);

        $data = json_decode(json_encode([
            'autoload' => [
                'psr-4' => [
                    'Workbench\\App\\' => ['workbench/app/', 'workbench/app-extra/'],
                ],
            ],
        ]));

        $composer->currentAsPackage = new Package($data);

        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App\\Services');

        $this->assertStringEndsWith('/workbench/app/Services', $reference->directory->toString());
    }

    #[Test]
    public function it_creates_a_test_companion_with_the_correct_name_and_namespace(): void
    {
        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App\\Services\\Billing');

        $this->assertInstanceOf(TestClass::class, $reference->test);
        $this->assertSame('InvoiceTest', $reference->test->name->toString());
        $this->assertSame(
            $reference->namespace->toString(),
            $reference->test->baseNamespace->toString(),
        );
    }

    #[Test]
    public function its_test_class_self_references_its_own_test(): void
    {
        $reference = new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App');
        $test = $reference->test;

        $this->assertSame($test, $test->test);
    }
}
