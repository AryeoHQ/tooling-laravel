<?php

declare(strict_types=1);

/**
 * PHPStan Bootstrap File
 * 
 * This file is loaded after the Laravel application is bootstrapped
 * and is used to define classes that may be referenced but don't exist in the codebase.
 */

// Define missing Builder classes that are referenced in UseEloquentBuilder attributes
if (!class_exists(\App\Models\MuxAssets\Builder::class, false)) {
    class_alias(
        \Illuminate\Database\Eloquent\Builder::class,
        \App\Models\MuxAssets\Builder::class
    );
}

// Define missing Collection classes that are referenced in CollectedBy attributes
if (!class_exists(\App\Models\MuxAssets\MuxAssets::class, false)) {
    class_alias(
        \Illuminate\Database\Eloquent\Collection::class,
        \App\Models\MuxAssets\MuxAssets::class
    );
}

