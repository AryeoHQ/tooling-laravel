<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Concerns\SearchesAutoloadCaches;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;

class MakeTestClass extends TestMakeCommand implements GeneratesFile
{
    use GeneratorCommandCompatibility;
    use SearchesAutoloadCaches;

    protected $description = 'Create a co-located test.';

    protected $type = 'Test';

    protected function collector(): string
    {
        return Untested::class;
    }

    private Stringable $classToTest {
        get => $this->classToTest ??= str($this->argument('class'));
    }

    public string $stub {
        get => __DIR__.'/stubs/test.stub';
    }

    public Stringable $nameInput {
        get => $this->reference->name;
    }

    private GenericClass $classReference {
        get => $this->classReference ??= GenericClass::fromFqcn($this->classToTest);
    }

    public Reference $reference {
        get => $this->classReference->test;
    }

    public function handle()
    {
        // Does not call parent::handle() to skip base command's operations
        return GeneratorCommand::handle();
    }

    protected function replaceClass($stub, $name)
    {
        return $stub;
    }

    protected function buildClass($name)
    {
        return str_replace([
            '{{ namespace }}',
            '{{ class }}',
        ], [
            $this->reference->namespace->after('\\')->toString(),
            $this->classReference->name->toString(),
        ], GeneratorCommand::buildClass($name));
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'class' => fn () => (string) \Laravel\Prompts\search(
                label: 'Which class would you like to test?',
                options: fn ($search) => $this->getClassSearchResults($search),
                required: true,
                scroll: 5,
            ),
        ];
    }

    /** @return array<int, InputArgument> */
    protected function getArguments(): array
    {
        return [
            new InputArgument('class', InputArgument::REQUIRED, 'The FQCN of the class to test.'),
        ];
    }

    /** @return array<int, InputOption> */
    protected function getOptions(): array
    {
        return [
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
        ];
    }

    /**
     * Override to skip base command prompts
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        //
    }
}
