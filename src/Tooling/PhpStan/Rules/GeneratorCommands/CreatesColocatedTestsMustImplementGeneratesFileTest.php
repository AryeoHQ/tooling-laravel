<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<CreatesColocatedTestsMustImplementGeneratesFile>
 */
class CreatesColocatedTestsMustImplementGeneratesFileTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new CreatesColocatedTestsMustImplementGeneratesFile;
    }

    #[Test]
    public function it_passes_when_class_does_not_use_colocated_tests(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_colocated_tests_does_not_implement_generates_file(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidColocatedTestsWithoutGeneratesFile.php')], [
            [
                'CreatesColocatedTests must implement GeneratesFile.',
                10,
            ],
        ]);
    }
}
