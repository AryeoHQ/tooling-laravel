<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

/**
 * @mixin \Tests\TestCase
 */
trait GeneratesFileTestCases
{
    /** @var class-string */
    public string $command {
        get => Str::beforeLast(static::class, 'Test');
    }

    protected string $expectedFilePath {
        get => $this->reference->filePath->toString();
    }

    #[Test]
    public function it_generates_a_file_with_the_correct_namespace(): void
    {
        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $contents = file_get_contents($this->expectedFilePath);

        $this->assertStringContainsString(
            'namespace '.$this->reference->namespace.';',
            $contents,
        );
    }
}
