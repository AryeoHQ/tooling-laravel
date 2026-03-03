<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\GeneratorCommands\GeneratorCommandCompatibilityMustImplementGeneratesFile;

/**
 * @extends RuleTestCase<GeneratorCommandCompatibilityMustImplementGeneratesFile>
 */
class GeneratorCommandCompatibilityMustImplementGeneratesFileTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new GeneratorCommandCompatibilityMustImplementGeneratesFile;
    }

    #[Test]
    public function it_passes_when_compatibility_implements_generates_file(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidGeneratesFileWithCompatibility.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_compatibility(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_compatibility_does_not_implement_generates_file(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidCompatibilityWithoutGeneratesFile.php')], [
            [
                'GeneratorCommandCompatibility must implement GeneratesFile.',
                10,
            ],
        ]);
    }
}
