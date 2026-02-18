<?php

declare(strict_types=1);

namespace Tests\Tooling\Console\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Console\Commands\MakeAnalyzer;

#[CoversClass(MakeAnalyzer::class)]
class MakeAnalyzerTest extends TestCase
{
    #[Test]
    public function it_can_make_a_phpstan_analyzer(): void
    {
        $this->artisan(MakeAnalyzer::class, ['library' => 'phpstan', 'name' => 'TestAnalyzer'])
            ->assertSuccessful();

        $this->assertFileExists(base_path('src/Tooling/PhpStan/Rules/TestAnalyzer.php'));

        $file = file_get_contents(base_path('src/Tooling/PhpStan/Rules/TestAnalyzer.php'));
        $this->assertStringContainsString('namespace Tooling\PhpStan\Rules;', $file);
        $this->assertStringContainsString('final class TestAnalyzer extends Rule', $file);
        $this->assertStringContainsString('public function shouldHandle(Node $node, Scope $scope): bool', $file);
        $this->assertStringContainsString('public function handle(Node $node, Scope $scope): void', $file);
    }

    #[Test]
    public function it_can_make_a_rector_analyzer(): void
    {
        $this->artisan(MakeAnalyzer::class, ['library' => 'rector', 'name' => 'TestAnalyzer'])
            ->assertSuccessful();

        $this->assertFileExists(base_path('src/Tooling/Rector/Rules/TestAnalyzer.php'));

        $file = file_get_contents(base_path('src/Tooling/Rector/Rules/TestAnalyzer.php'));
        $this->assertStringContainsString('namespace Tooling\Rector\Rules;', $file);
        $this->assertStringContainsString('final class TestAnalyzer extends Rule', $file);
        $this->assertStringContainsString('public function shouldHandle(Node $node): bool', $file);
        $this->assertStringContainsString('public function handle(Node $node): Node', $file);
    }
}
