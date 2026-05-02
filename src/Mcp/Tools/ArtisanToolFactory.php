<?php

declare(strict_types=1);

namespace Mcp\Tools;

use Illuminate\Console\Command;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\Console\Input\InputOption;

class ArtisanToolFactory
{
    /** @var list<string> */
    private const array SKIP_OPTIONS = [
        'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env',
    ];

    public static function make(Command $command): Tool
    {
        $commandName = $command->getName();
        $toolName = 'artisan_'.str_replace(':', '_', $commandName);
        $description = $command->getDescription();
        $definition = $command->getDefinition();

        $arguments = collect($definition->getArguments())->all();
        $options = collect($definition->getOptions())
            ->reject(fn (InputOption $option) => in_array($option->getName(), self::SKIP_OPTIONS))
            ->all();

        return new class ($toolName, $description, $commandName, $arguments, $options) extends Tool
        {
            /**
             * @param  array<string, \Symfony\Component\Console\Input\InputArgument>  $commandArguments
             * @param  array<string, \Symfony\Component\Console\Input\InputOption>  $commandOptions
             */
            public function __construct(
                private string $toolName,
                private string $toolDescription,
                private string $commandName,
                private array $commandArguments,
                private array $commandOptions,
            ) {}

            public function name(): string
            {
                return $this->toolName;
            }

            public function title(): string
            {
                return $this->toolDescription;
            }

            public function description(): string
            {
                return $this->toolDescription;
            }

            public function schema(JsonSchema $schema): array
            {
                $properties = [];

                foreach ($this->commandArguments as $argument) {
                    $type = $argument->isArray()
                        ? $schema->array()->items($schema->string())
                        : $schema->string();

                    $type = $type->description($argument->getDescription());

                    if ($argument->isRequired()) {
                        $type = $type->required();
                    }

                    if ($argument->getDefault() !== null) {
                        $type = $type->default($argument->getDefault());
                    }

                    $properties[$argument->getName()] = $type;
                }

                foreach ($this->commandOptions as $option) {
                    $type = match (true) {
                        ! $option->acceptValue() => $schema->boolean()->default(false),
                        $option->isArray() => $schema->array()->items($schema->string())->default($option->getDefault() ?? []),
                        $option->isValueRequired() => $schema->string(),
                        default => $schema->string()->default((string) ($option->getDefault() ?? '')),
                    };

                    $type = $type->description($option->getDescription());

                    $properties[$option->getName()] = $type;
                }

                return $properties;
            }

            public function handle(Request $request): Response|ResponseFactory
            {
                $params = [];

                foreach ($this->commandArguments as $argument) {
                    $value = $request->get($argument->getName());
                    if ($value !== null) {
                        $params[$argument->getName()] = $value;
                    }
                }

                foreach ($this->commandOptions as $option) {
                    $value = $request->get($option->getName());
                    if ($value !== null) {
                        $params['--'.$option->getName()] = $value;
                    }
                }

                $exitCode = Artisan::call($this->commandName, $params);
                $output = Artisan::output();

                $decoded = json_decode(trim($output), true);

                return Response::structured(
                    $decoded ?? ['output' => trim($output), 'exit_code' => $exitCode]
                );
            }
        };
    }
}
