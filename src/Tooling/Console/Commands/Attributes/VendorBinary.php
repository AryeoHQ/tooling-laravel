<?php

declare(strict_types=1);

namespace Tooling\Console\Commands\Attributes;

use Attribute;
use Closure;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Tooling\Composer\Composer;
use Tooling\Console\Inspectors\Inspector;

#[Attribute(Attribute::TARGET_CLASS)]
class VendorBinary
{
    public readonly string $binary;

    public readonly null|string $command;

    public readonly Inspector $inspector;

    protected Composer $composer { get => $this->composer ??= resolve(Composer::class); }

    protected string $executable { get => $this->executable ??= $this->composer->vendorPath('bin', $this->binary); }

    public function __construct(string $inspector, string $binary, null|string $command = null)
    {
        $this->binary = $binary;
        $this->command = $command;
        $this->inspector = resolve($inspector)->executable($this->executable);
    }

    /**
     * @param  Collection<array-key, mixed>  $arguments
     * @param  Collection<array-key, mixed>  $options
     */
    public function run(Collection $arguments, Collection $options, null|OutputInterface $output = null): ProcessResult
    {
        $command = collect([$this->executable, $this->command])->filter()->merge(
            $arguments->skip(1)->flatten()->values()->reject(
                fn (string $argument) => str($argument)->is($this->command)
            )->reject(
                fn (string $argument) => $this->inspector->aliases->contains($argument)
            )
        )->concat($options);

        return Process::path($this->composer->baseDirectory->toString())
            ->tty($output ? $output->isDecorated() : false)
            ->forever()
            ->run(
                $command->toArray(),
                $this->ttyFallback($output)
            );
    }

    public function ttyFallback(null|\Symfony\Component\Console\Output\OutputInterface $output = null): Closure
    {
        return function (string $type, string $data) use ($output) {
            if ($output) {
                $output->write($data);
            } else {
                echo $data;
            }
        };
    }
}
