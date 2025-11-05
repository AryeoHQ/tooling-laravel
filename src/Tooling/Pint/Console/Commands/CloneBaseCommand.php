<?php

declare(strict_types=1);

namespace Tooling\Pint\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;
use Tooling\Composer\Composer;

class CloneBaseCommand extends Command
{
    protected $name = 'tooling:pint:clone-base-command';

    protected $description = 'Clone the base Pint command for inspection';

    protected $hidden = true;

    private Composer $composer {
        get => $this->composer ??= new Composer;
    }

    /** @var ReflectionClass<Pint> */
    protected ReflectionClass $wrapperCommandReflection { get => $this->wrapperCommandReflection ??= new ReflectionClass(Pint::class); }

    protected false|string $wrapperCommandPath { get => $this->wrapperCommandPath ??= $this->wrapperCommandReflection->getFileName(); }

    protected false|string $baseCommandPath {
        get => $this->composer->vendorPath('laravel/pint/app/Commands/DefaultCommand.php');
    }

    protected null|SplFileInfo $baseCommandFile {
        get => when($this->baseCommandPath, fn ($path) => new SplFileInfo($path, '', basename($path)));
    }

    protected null|Stringable $baseCommandContent {
        get => when($this->baseCommandFile, fn ($file) => str($file->getContents()));
    }

    public function handle(): void
    {
        throw_unless($this->baseCommandPath, 'Laravel Pint is not installed.');
        throw_unless($this->wrapperCommandPath, 'Laravel Pint wrapper command not found.');

        File::put(
            dirname($this->wrapperCommandPath).'/Fixtures/DefaultCommand.php',
            $this->localizeCommandContent()->toString()
        );
    }

    public function localizeCommandContent(): Stringable
    {
        return $this->baseCommandContent->replace(
            '<?php',
            '<?php'.PHP_EOL.PHP_EOL.'declare(strict_types=1);'
        )->replace(
            'namespace App\Commands;',
            'namespace '.$this->wrapperCommandReflection->getNamespaceName().'\Fixtures;'
        )->replace(
            'use LaravelZero\Framework\Commands\Command;',
            'use '.Command::class.';'
        )->replace(
            '* @param  \App\Actions\FixCode  $fixCode',
            '* @param  object  $fixCode'
        )->replace(
            '* @param  \App\Actions\ElaborateSummary  $elaborateSummary',
            '* @param  object  $elaborateSummary'
        );
    }
}
