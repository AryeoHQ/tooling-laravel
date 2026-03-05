<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands\Make;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Concerns\RetrievesNamespace;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\PhpStan\Console\Commands\Make\References\PhpStanRule;

#[AsCommand(name: 'make:phpstan:rule', description: 'Make a new PHPStan rule')]
class MakeRule extends GeneratorCommand implements GeneratesFile
{
    use CreatesColocatedTests;
    use GeneratorCommandCompatibility;
    use RetrievesNamespace;

    protected $type = 'PHPStan Rule';

    public string $stub {
        get => __DIR__.'/stubs/rule.stub';
    }

    public Stringable $nameInput {
        get => $this->nameInput ??= str($this->argument('name'));
    }

    public Reference $reference {
        get => $this->reference ??= new PhpStanRule(
            name: $this->nameInput,
            baseNamespace: $this->baseNamespace,
        );
    }

    public function handle(): null|bool
    {
        $this->resolveNamespace();

        return parent::handle();
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
            ...$this->getNamespaceInputOptions(),
        ];
    }
}
