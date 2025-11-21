<?php

/**
 * Dynamic PHPStan Paths Configuration
 *
 * This loader determines which paths PHPStan should analyze and scan for type information.
 * Importantly, these paths are used by Larastan to discover Eloquent models and relationships.
 *
 * Priority:
 * 1. PHPSTAN_PATHS environment variable (if set)
 * 2. Smart detection:
 *    - Laravel apps: 'app/' directory
 *    - Packages: 'src/' directory
 *    - Fallback: current directory
 */

use Tooling\Composer\Composer;

use function Illuminate\Filesystem\join_paths;

$composer = new Composer;
$baseDir = $composer->baseDirectory;

// Check for explicit path configuration via environment
$envPaths = env('PHPSTAN_PATHS');
if ($envPaths) {
    // Use explicitly configured paths
    $paths = explode(',', $envPaths);
} else {
    // Auto-detect based on project structure
    $appDir = join_paths($baseDir, 'app');
    $srcDir = join_paths($baseDir, 'src');
    
    if (is_dir($appDir)) {
        // Laravel application structure detected
        $paths = ['app'];
    } elseif (is_dir($srcDir)) {
        // Package/library structure detected
        $paths = ['src'];
    } else {
        // No standard structure found, analyze everything
        $paths = ['.'];
    }
}

return [
    'parameters' => [
        'paths' => $paths,
    ],
];

