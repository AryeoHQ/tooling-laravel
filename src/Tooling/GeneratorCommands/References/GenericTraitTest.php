<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\GeneratorCommands\References\Concerns\ManagesNamespaceTestCases;
use Tooling\GeneratorCommands\References\Concerns\ResolvesPathsTestCases;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

#[CoversClass(GenericTrait::class)]
class GenericTraitTest extends TestCase implements TestsReference
{
    use ManagesNamespaceTestCases;
    use ReferenceTestCases;
    use ResolvesPathsTestCases;

    public Reference $subject {
        get => new GenericTrait(name: 'Exportable', baseNamespace: 'Workbench\\App\\Concerns');
    }

    public string $expectedName = 'Exportable';

    #[Test]
    public function it_creates_a_test_companion_with_the_correct_name_and_namespace(): void
    {
        $reference = new GenericTrait(name: 'Exportable', baseNamespace: 'Workbench\\App\\Concerns');

        $this->assertInstanceOf(TestCasesTrait::class, $reference->test);
        $this->assertSame('ExportableTestCases', $reference->test->name->toString());
        $this->assertSame(
            $reference->namespace->toString(),
            $reference->test->baseNamespace->toString(),
        );
    }

    #[Test]
    public function its_test_cases_trait_self_references_its_own_test(): void
    {
        $reference = new GenericTrait(name: 'Exportable', baseNamespace: 'Workbench\\App');
        $test = $reference->test;

        $this->assertSame($test, $test->test);
    }
}
