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
    if (str_ends_with($class, 'Builder')) {
        // Create an alias for custom Eloquent builders
        class_alias(\Illuminate\Database\Eloquent\Builder::class, $class);
    } elseif (str_ends_with($class, 'Collection') || str_contains($class, '\\' . basename(dirname($class)) . 's')) {
        // Create an alias for custom collections (e.g., App\Models\MuxAssets\MuxAssets)
        class_alias(\Illuminate\Database\Eloquent\Collection::class, $class);
    } elseif (str_contains($class, '\\Enums\\')) {
        // Create a dummy enum for missing enums
        eval(sprintf(
            'namespace %s; enum %s: string {}',
            substr($class, 0, strrpos($class, '\\')),
            substr($class, strrpos($class, '\\') + 1)
        ));
    }
}, true, true);

