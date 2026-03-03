<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\GeneratorCommands\RetrievesNamespaceFromInputMustUseSearchesNamespaces;

/**
 * @extends RuleTestCase<RetrievesNamespaceFromInputMustUseSearchesNamespaces>
 */
class RetrievesNamespaceFromInputMustUseSearchesNamespacesTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RetrievesNamespaceFromInputMustUseSearchesNamespaces;
    }

    #[Test]
    public function it_passes_when_class_does_not_use_retrieves_namespace(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_retrieves_namespace_does_not_use_searches_namespaces(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidRetrievesNamespaceWithoutSearchesNamespaces.php')], [
            [
                'RetrievesNamespaceFromInput must use SearchesNamespaces.',
                10,
            ],
        ]);
    }
}
