<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands\Make;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Concerns\GeneratesFileTestCases;
use Tooling\GeneratorCommands\Testing\Concerns\RetrievesNamespaceTestCases;
use Tooling\Rector\Console\Commands\Make\References\RectorRule;

#[CoversClass(MakeRule::class)]
class MakeRuleTest extends TestCase
{
    use GeneratesFileTestCases;
    use RetrievesNamespaceTestCases;

    public Reference $reference {
        get => new RectorRule(name: 'TestRule', baseNamespace: 'App');
    }

    private Reference $nestedReference {
        get => new RectorRule(name: 'TestRule', baseNamespace: 'App\\Nested\\Deeper');
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

    /** @var array<string, mixed> */
    public array $withNestedNamespaceInput {
        get => ['name' => 'TestRule', '--namespace' => 'App\\Nested\\Deeper'];
    }

    protected string $expectedNestedFilePath {
        get => $this->nestedReference->filePath->toString();
    }

    #[Test]
    public function it_generates_a_rector_rule(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $file = File::get($this->expectedFilePath);

        $this->assertStringContainsString('final class TestRule extends Rule', $file);
        $this->assertStringContainsString('public function shouldHandle(Node $node): bool', $file);
        $this->assertStringContainsString('public function handle(Node $node): Node', $file);
    }
}
