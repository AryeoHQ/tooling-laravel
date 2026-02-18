<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

use function Orchestra\Testbench\package_path;

#[AsCommand(name: 'tooling:make-analyzer', description: 'Make a new analyzer')]
class MakeAnalyzer extends GeneratorCommand
{
    protected string $rulesPath;

    protected $type = 'Tooling';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->rulesPath = $this->findOrCreateLibraryRulesDirectory($this->argument('library'));

        parent::handle();

        return null;
    }

    protected function getStub()
    {
        $library = $this->argument('library');

        return match ($library) {
            'phpstan', 'rector' => __DIR__."/stubs/{$library}.stub",
            default => throw new InvalidArgumentException('Invalid library'),
        };
    }

    public function getArguments()
    {
        return [
            ['library', InputArgument::REQUIRED, 'The analyzer library the rule is for (phpstan, rector)'],
            ['name', InputArgument::REQUIRED, 'The name of the analyzer'],
        ];
    }

    protected function rootNamespace()
    {
        return Str::of($this->rulesPath)
            ->after('src/')
            ->replace('/', '\\')
            ->toString();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    protected function getPath($name)
    {
        return Str::of($this->getBasePath())
            ->before('/Tooling')
            ->append('/'.$this->rootNamespace().'/'.$this->getNameInput().'.php')
            ->replace('\\', '/')
            ->toString();
    }

    private function findOrCreateLibraryRulesDirectory(string $library): string
    {
        $libraryPath = null;
        $rulesPath = null;

        $basePath = $this->getBasePath();

        if (! is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $directoryIterator = new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if ($file->isDir() && strtolower($file->getFilename()) === strtolower((string) $this->argument('library'))) {
                $libraryPath = $file->getPathname();
                break;
            }
        }

        $rulesPath = match (true) {
            $libraryPath !== null => $libraryPath.'/Rules',
            default => $basePath.'/'.$this->getLibraryFolderName($library).'/Rules',
        };

        if (! is_dir($rulesPath)) {
            mkdir($rulesPath, 0755, true);
        }

        return $rulesPath;
    }

    private function getLibraryFolderName(string $library): string
    {
        return match ($library) {
            'phpstan' => 'PhpStan',
            default => ucfirst($library),
        };
    }

    private function getBasePath(): string
    {
        return match (app()->environment()) {
            'testing' => base_path('src/Tooling'),
            default => package_path('src/Tooling'),
        };
    }
}
