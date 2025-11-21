<?php

declare(strict_types=1);

/**
 * PHPStan Bootstrap File
 * 
 * This file is loaded before PHPStan analysis begins and is used to define
 * classes that may be referenced but don't exist in the codebase.
 */

// Only define the class if it doesn't already exist
if (!class_exists(\App\Models\MuxAssets\Builder::class)) {
    /**
     * Stub for missing custom Eloquent Builder class
     * 
     * @template TModelClass of \Illuminate\Database\Eloquent\Model
     * @extends \Illuminate\Database\Eloquent\Builder<TModelClass>
     */
    class_alias(
        \Illuminate\Database\Eloquent\Builder::class,
        \App\Models\MuxAssets\Builder::class
    );
}

