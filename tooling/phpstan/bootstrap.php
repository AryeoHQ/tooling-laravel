<?php

declare(strict_types=1);

/**
 * PHPStan Bootstrap File
 * 
 * This file is loaded after the Laravel application is bootstrapped
 * and registers a custom autoloader to handle missing classes gracefully.
 */

// Register a custom autoloader to prevent PHPStan from crashing on missing classes
spl_autoload_register(function (string $class): void {
    // Only handle classes that don't exist and are in the App namespace
    if (class_exists($class, false) || interface_exists($class, false) || enum_exists($class, false)) {
        return;
    }

    // Only intercept App\Models classes to avoid interfering with other autoloading
    if (!str_starts_with($class, 'App\\Models\\')) {
        return;
    }

    // Determine what type of class to create based on naming patterns
    $className = substr($class, strrpos($class, '\\') + 1);
    $namespace = substr($class, 0, strrpos($class, '\\'));
    $parentDir = basename($namespace);

    if (str_ends_with($className, 'Builder')) {
        // Create an alias for custom Eloquent builders
        class_alias(\Illuminate\Database\Eloquent\Builder::class, $class);
    } elseif (str_ends_with($className, 'Collection') || $className === $parentDir) {
        // Create an alias for custom collections
        // Matches patterns like:
        // - App\Models\Posts\PostCollection
        // - App\Models\MuxAssets\MuxAssets (where class name matches parent directory)
        class_alias(\Illuminate\Database\Eloquent\Collection::class, $class);
    } elseif (str_contains($namespace, '\\Enums')) {
        // Create a dummy enum for missing enums
        eval(sprintf(
            'namespace %s; enum %s: string {}',
            $namespace,
            $className
        ));
    }
}, true, true);

