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
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Concerns\SearchesClasses;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;

class MakeTestClass extends TestMakeCommand implements GeneratesFile
{
    use GeneratorCommandCompatibility;
    use SearchesClasses;

    protected $description = 'Create a co-located test.';

    protected $type = 'Test';

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
        get => $this->classReference ??= new GenericClass($this->classToTest);
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
            '{{ fqcn }}',
            '{{ class }}',
        ], [
            $this->reference->namespace->toString(),
            $this->classReference->fqcn->toString(),
            $this->classReference->name->toString(),
        ], GeneratorCommand::buildClass($name));
    }

    protected function promptForMissingArgumentsUsing(): array // @phpstan-ignore method.childReturnType
    {
        return [ // @phpstan-ignore return.type
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
