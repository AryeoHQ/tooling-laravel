<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\GeneratorCommands\SearchesClassesMustUseSearchesNamespaces;

/**
 * @extends RuleTestCase<SearchesClassesMustUseSearchesNamespaces>
 */
class SearchesClassesMustUseSearchesNamespacesTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SearchesClassesMustUseSearchesNamespaces;
    }

    #[Test]
    public function it_passes_when_class_does_not_use_searches_classes(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_searches_classes_does_not_use_searches_namespaces(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidSearchesClassesWithoutSearchesNamespaces.php')], [
            [
                'SearchesClasses must use SearchesNamespaces.',
                10,
            ],
        ]);
    }
}
