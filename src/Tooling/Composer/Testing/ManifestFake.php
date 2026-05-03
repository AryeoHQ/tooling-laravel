<?php

declare(strict_types=1);

namespace Tooling\Composer\Testing;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Tooling\Composer\Composer;
use Tooling\Composer\Manifest;

use function Illuminate\Filesystem\join_paths;

final class ManifestFake extends Manifest
{
    public static function make(): static
    {
        return new self;
    }

    public function withRectorConfig(): static
    {
        $rectorRulesPath = 'tooling/rector/rules.php';
        $rectorConfiguredRulesPath = 'tooling/rector/configured-rules.php';
        $rectorSkipPath = 'tooling/rector/skip.php';

        $fake = Composer::fake()->merge(['extra' => ['tooling' => ['rector' => [
            'rules' => $rectorRulesPath,
            'configured_rules' => $rectorConfiguredRulesPath,
            'skip' => $rectorSkipPath,
        ]]]]);

        $base = $fake->baseDirectory->toString();

        File::put(join_paths($base, $rectorRulesPath), "<?php return ['FakeRuleOne', 'FakeRuleTwo'];");
        File::put(join_paths($base, $rectorConfiguredRulesPath), "<?php return ['FakeConfiguredRule' => ['key' => 'value']];");
        File::put(join_paths($base, $rectorSkipPath), "<?php return ['FakeSkipRule' => ['path/to/file.php']];");

        return $this;
    }

    public function withPhpStanConfig(): static
    {
        $phpstanPath = 'tooling/phpstan/rules.neon';

        $fake = Composer::fake()->merge(['extra' => ['tooling' => ['phpstan' => ['rules' => $phpstanPath]]]]);

        $files = resolve(Filesystem::class);
        $base = $fake->baseDirectory->toString();

        $files->put(join_paths($base, $phpstanPath), "parameters:\n    reportUnmatchedIgnoredErrors: false");

        return $this;
    }
}
