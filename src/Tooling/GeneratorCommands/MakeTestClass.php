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
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\Concerns\SearchesClasses;
use Tooling\GeneratorCommands\Concerns\SearchesNamespaces;

class MakeTestClass extends TestMakeCommand
{
    use SearchesClasses;
    use SearchesNamespaces;

    protected $description = 'Create a co-located test.';

    protected $type = 'Test';

    private Stringable $classToTest {
        get => $this->classToTest ??= str($this->argument('class'));
    }

    private Stringable $classNamespace {
        get => $this->classToTest->beforeLast('\\');
    }

    private Stringable $className {
        get => str(class_basename($this->classToTest->toString()));
    }

    private Stringable $testName {
        get => $this->className->append('Test');
    }

    private Stringable $directoryPath {
        get => $this->directoryPath ??= $this->resolveDirectoryPath();
    }

    public function handle()
    {
        // Does not call parent::handle() to skip base command's operations
        return GeneratorCommand::handle();
    }

    public function getStub(): string
    {
        return __DIR__.'/stubs/test.stub';
    }

    protected function getNameInput(): string
    {
        return $this->testName->toString();
    }

    protected function rootNamespace()
    {
        return $this->classNamespace->toString();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->rootNamespace();
    }

    protected function getPath($name): string
    {
        return $this->directoryPath->append('/', $this->testName->toString(), '.php')->toString();
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
            $this->classNamespace->toString(),
            $this->classToTest->toString(),
            $this->className->toString(),
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

    private function resolveDirectoryPath(): Stringable
    {
        $composer = resolve(Composer::class);

        $filePath = $composer->classMap->get($this->classToTest->toString());

        if (is_string($filePath) && file_exists($filePath)) {
            return str(dirname($filePath));
        }

        $namespace = $this->classNamespace->append('\\')->toString();

        $matchedPrefix = null;
        $matchedPath = null;

        foreach ($this->availableNamespaces as $prefix => $basePath) {
            $prefix = (string) $prefix;
            $basePath = (string) $basePath;

            if (str_starts_with($namespace, $prefix) && ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix))) {
                $matchedPrefix = $prefix;
                $matchedPath = $basePath;
            }
        }

        $relative = ltrim(str_replace('\\', '/', substr($namespace, strlen($matchedPrefix ?? ''))), '/');
        $base = str($matchedPath ?? $this->laravel->basePath('app'));

        if (! $base->startsWith('/')) {
            $base = str($composer->baseDirectory->toString())->append('/', $base->rtrim('/')->toString());
        }

        return $relative === '' ? $base : $base->rtrim('/')->append('/', rtrim($relative, '/'));
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
