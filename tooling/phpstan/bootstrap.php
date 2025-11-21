<?php

declare(strict_types=1);

/**
 * PHPStan Bootstrap File
 * 
 * This file is loaded before PHPStan analysis and creates aliases/stubs for missing classes.
 */

// Directly create class aliases for common missing patterns
// This prevents PHPStan from crashing when models reference classes that don't exist

// MuxAssets - Collection class
if (!class_exists(\App\Models\MuxAssets\MuxAssets::class, false)) {
    class_alias(\Illuminate\Database\Eloquent\Collection::class, \App\Models\MuxAssets\MuxAssets::class);
}

// MuxAssets - Builder class
if (!class_exists(\App\Models\MuxAssets\Builder::class, false)) {
    class_alias(\Illuminate\Database\Eloquent\Builder::class, \App\Models\MuxAssets\Builder::class);
}

// MuxAssets - Enum
if (!enum_exists(\App\Models\MuxAssets\Enums\MuxAssetStatus::class, false)) {
    eval('namespace App\Models\MuxAssets\Enums; enum MuxAssetStatus: string { case Preparing = "preparing"; case Ready = "ready"; case Errored = "errored"; }');
}

