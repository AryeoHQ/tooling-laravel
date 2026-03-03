<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\GeneratorCommands\GeneratesFileMustExtendGeneratorCommand;

/**
 * @extends RuleTestCase<GeneratesFileMustExtendGeneratorCommand>
 */
class GeneratesFileMustExtendGeneratorCommandTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new GeneratesFileMustExtendGeneratorCommand;
    }

    #[Test]
    public function it_passes_when_generates_file_extends_generator_command(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidGeneratesFileWithCompatibility.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_implement_generates_file(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_generates_file_does_not_extend_generator_command(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidGeneratesFileWithoutGeneratorCommand.php')], [
            [
                'GeneratesFile must extend GeneratorCommand.',
                11,
            ],
        ]);
    }
}
