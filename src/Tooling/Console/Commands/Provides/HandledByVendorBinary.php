<?php

declare(strict_types=1);

namespace Tooling\Console\Commands\Provides;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use LogicException;
use ReflectionObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Commands\Attributes\VendorBinary;

/**
 * @mixin Command
 */
trait HandledByVendorBinary
{
    protected ReflectionObject $reflection { get => $this->reflection ??= new ReflectionObject($this); }

    protected VendorBinary $vendorBinary {
        get => $this->vendorBinary ??= throw_unless(
            data_get($this->reflection->getAttributes(VendorBinary::class), 0)?->newInstance(),
            LogicException::class,
        );
    }

    /** @var Collection<array-key, mixed> */
    protected Collection $arguments {
        get => $this->arguments ??= collect($this->arguments());
    }

    /** @var Collection<array-key, string> */
    protected Collection $options {
        get => collect($this->options())->filter()->reject(
            fn ($value, $name): bool => $this->notForwardable->contains($name)
        )->map(
            fn ($value, $name): string => $value === true ? "--{$name}" : "--{$name}={$value}"
        );
    }

    /** @var Collection<array-key, string> */
    protected Collection $notForwardable {
        get => $this->notForwardable ??= collect(['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env']);
    }

    public function handle(): int
    {
        $result = $this->vendorBinary->run($this->arguments, $this->options, tty: $this->output->isDecorated());

        return $result->successful() ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @return array<array-key, InputOption>
     */
    protected function getOptions(): array
    {
        return $this->vendorBinary->inspector->options->toArray();
    }

    /**
     * @return array<array-key, InputArgument>
     */
    protected function getArguments(): array
    {
        return $this->vendorBinary->inspector->arguments->toArray();
    }
}
