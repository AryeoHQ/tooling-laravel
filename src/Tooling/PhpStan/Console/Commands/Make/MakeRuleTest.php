<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands\Make;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Concerns\CleansUpGeneratorCommands;
use Tooling\GeneratorCommands\Testing\Concerns\GeneratesFileTestCases;
use Tooling\GeneratorCommands\Testing\Concerns\RetrievesNamespaceTestCases;
use Tooling\PhpStan\Console\Commands\Make\References\PhpStanRule;

#[CoversClass(MakeRule::class)]
class MakeRuleTest extends TestCase
{
    use CleansUpGeneratorCommands;
    use GeneratesFileTestCases;
    use RetrievesNamespaceTestCases;

    public Reference $reference {
        get => new PhpStanRule(name: 'TestRule', baseNamespace: 'App');
    }

    /** @var array<string, mixed> */
    public array $baselineInput {
        get => ['name' => 'TestRule', '--namespace' => 'App\\'];
    }

    /** @var array<string, mixed> */
    public array $withNamespaceBackslashInput {
        get => $this->baselineInput;
    }

    /** @var array<string, mixed> */
    public array $withoutNamespaceBackslashInput {
        get => ['name' => 'TestRule', '--namespace' => 'App'];
    }

    #[Test]
    public function it_generates_a_phpstan_rule(): void
    {
        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $file = file_get_contents($this->expectedFilePath);

        $this->assertStringContainsString('final class TestRule extends Rule', $file);
        $this->assertStringContainsString('public function shouldHandle(Node $node, Scope $scope): bool', $file);
        $this->assertStringContainsString('public function handle(Node $node, Scope $scope): void', $file);
    }
}
