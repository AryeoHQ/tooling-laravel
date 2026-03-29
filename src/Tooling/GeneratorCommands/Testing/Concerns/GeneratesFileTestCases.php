<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tooling\Composer\Composer;

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
        Composer::fake();

        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $contents = File::get($this->expectedFilePath);

        $this->assertStringContainsString(
            'namespace '.$this->reference->namespace->after('\\').';',
            $contents,
        );
    }
}
