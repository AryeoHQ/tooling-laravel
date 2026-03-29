<?php

declare(strict_types=1);

namespace Tooling\Filesystem\Testing;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class FilesystemFake extends Filesystem
{
    /** @var array<string, string> */
    private array $files = [];

    /** @var array<string, true> */
    private array $directories = [];

    /** @var array<string, int> */
    private array $timestamps = [];

    /** @var array<int, string> */
    private array $fakedPaths = [];

    /**
     * @param  string|array<int, string>  $paths
     */
    public function __construct(string|array $paths = [])
    {
        $this->addFakedPaths($paths);
    }

    /**
     * @param  string|array<int, string>  $paths
     */
    public function addFakedPaths(string|array $paths): static
    {
        $this->fakedPaths = array_values(array_unique([
            ...$this->fakedPaths,
            ...(array) $paths,
        ]));

        return $this;
    }

    private function isFaked(string $path): bool
    {
        return str($path)->is($this->fakedPaths);
    }

    private function ensureParentDirectoriesExist(string $path): void
    {
        $dir = dirname($path);

        while ($dir !== '.' && $dir !== '/' && $dir !== '') {
            $this->directories[$dir] = true;
            $this->timestamps[$dir] ??= now()->timestamp;
            $dir = dirname($dir);
        }
    }

    public function exists($path)
    {
        if (! $this->isFaked($path)) {
            return parent::exists($path);
        }

        return array_key_exists($path, $this->files) || array_key_exists(rtrim($path, '/'), $this->directories);
    }

    public function get($path, $lock = false)
    {
        if (! $this->isFaked($path)) {
            return parent::get($path, $lock);
        }

        if (! array_key_exists($path, $this->files)) {
            throw new FileNotFoundException("File does not exist at path {$path}.");
        }

        return $this->files[$path];
    }

    /** @return array<string, mixed>|null */
    public function json($path, $flags = 0, $lock = false) // @phpstan-ignore method.childReturnType
    {
        if (! $this->isFaked($path)) {
            return parent::json($path, $flags, $lock);
        }

        return json_decode($this->get($path), true, 512, $flags);
    }

    public function put($path, $contents, $lock = false)
    {
        if (! $this->isFaked($path)) {
            return parent::put($path, $contents, $lock);
        }

        $this->files[$path] = $contents;
        $this->timestamps[$path] = now()->timestamp;
        $this->ensureParentDirectoriesExist($path);
        $this->timestamps[dirname($path)] = now()->timestamp;

        return strlen($contents);
    }

    public function append($path, $data, $lock = false)
    {
        if (! $this->isFaked($path)) {
            return parent::append($path, $data, $lock);
        }

        $existing = $this->files[$path] ?? '';
        $this->files[$path] = $existing.$data;
        $this->timestamps[$path] = now()->timestamp;
        $this->ensureParentDirectoriesExist($path);

        return strlen($data);
    }

    /** @param  string|array<int, string>  $paths */
    public function delete($paths) // @phpstan-ignore method.childParameterType
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $real = [];

        foreach ($paths as $path) {
            if ($this->isFaked($path)) {
                unset($this->files[$path], $this->timestamps[$path]);
            } else {
                $real[] = $path;
            }
        }

        if ($real !== []) {
            parent::delete($real);
        }

        return true;
    }

    public function copy($path, $target)
    {
        $sourceFaked = $this->isFaked($path);
        $targetFaked = $this->isFaked($target);

        if (! $sourceFaked && ! $targetFaked) {
            return parent::copy($path, $target);
        }

        $contents = $sourceFaked
            ? ($this->files[$path] ?? null)
            : (parent::exists($path) ? parent::get($path) : null);

        if ($contents === null) {
            return false;
        }

        if ($targetFaked) {
            $this->files[$target] = $contents;
            $this->timestamps[$target] = now()->timestamp;
            $this->ensureParentDirectoriesExist($target);
        } else {
            parent::put($target, $contents);
        }

        return true;
    }

    public function move($path, $target)
    {
        $sourceFaked = $this->isFaked($path);
        $targetFaked = $this->isFaked($target);

        if (! $sourceFaked && ! $targetFaked) {
            return parent::move($path, $target);
        }

        $contents = $sourceFaked
            ? ($this->files[$path] ?? null)
            : (parent::exists($path) ? parent::get($path) : null);

        if ($contents === null) {
            return false;
        }

        if ($targetFaked) {
            $this->files[$target] = $contents;
            $this->timestamps[$target] = now()->timestamp;
            $this->ensureParentDirectoriesExist($target);
        } else {
            parent::put($target, $contents);
        }

        if ($sourceFaked) {
            unset($this->files[$path]);
        } else {
            parent::delete($path);
        }

        return true;
    }

    public function chmod($path, $mode = null)
    {
        if (! $this->isFaked($path)) {
            return parent::chmod($path, $mode);
        }

        return $mode ?? ''; // no-op for in-memory fake
    }

    public function isFile($file)
    {
        if (! $this->isFaked($file)) {
            return parent::isFile($file);
        }

        return array_key_exists($file, $this->files);
    }

    public function isDirectory($directory)
    {
        if (! $this->isFaked($directory)) {
            return parent::isDirectory($directory);
        }

        return array_key_exists(rtrim($directory, '/'), $this->directories);
    }

    public function isEmptyDirectory($directory, $ignoreDotFiles = false)
    {
        if (! $this->isFaked($directory)) {
            return parent::isEmptyDirectory($directory, $ignoreDotFiles);
        }

        $normalized = rtrim($directory, '/');

        if (! array_key_exists($normalized, $this->directories)) {
            return false;
        }

        $prefix = $normalized.'/';

        foreach ($this->files as $path => $_) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        foreach ($this->directories as $dir => $_) {
            if ($dir !== $normalized && str_starts_with($dir, $prefix)) {
                return false;
            }
        }

        return true;
    }

    public function lastModified($path)
    {
        if (! $this->isFaked($path)) {
            return parent::lastModified($path);
        }

        return $this->timestamps[$path]
            ?? $this->timestamps[rtrim($path, '/')]
            ?? throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * @return array<int, string>
     */
    public function glob($pattern, $flags = 0)
    {
        $regex = $this->globToRegex($pattern);
        $matches = [];

        // In-memory matches
        foreach (array_keys($this->files) as $path) {
            if (preg_match($regex, $path)) {
                $matches[] = $path;
            }
        }

        // Real filesystem matches for unfaked paths
        foreach (parent::glob($pattern, $flags) as $path) {
            if (! $this->isFaked($path)) {
                $matches[] = $path;
            }
        }

        $matches = array_unique($matches);
        sort($matches);

        return $matches;
    }

    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        if (! $this->isFaked($path)) {
            return parent::makeDirectory($path, $mode, $recursive, $force);
        }

        $normalized = rtrim($path, '/');
        $this->directories[$normalized] = true;
        $this->timestamps[$normalized] = now()->timestamp;
        $this->ensureParentDirectoriesExist($normalized.'/dummy');

        return true;
    }

    public function ensureDirectoryExists($path, $mode = 0755, $recursive = true)
    {
        if (! $this->isFaked($path)) {
            parent::ensureDirectoryExists($path, $mode, $recursive);

            return;
        }

        if (! $this->isDirectory($path)) {
            $this->makeDirectory($path, $mode, $recursive);
        }
    }

    public function deleteDirectory($directory, $preserve = false)
    {
        if (! $this->isFaked($directory)) {
            return parent::deleteDirectory($directory, $preserve);
        }

        $normalized = rtrim($directory, '/');
        $prefix = $normalized.'/';

        foreach (array_keys($this->files) as $path) {
            if (str_starts_with($path, $prefix)) {
                unset($this->files[$path], $this->timestamps[$path]);
            }
        }

        foreach (array_keys($this->directories) as $dir) {
            if ($dir === $normalized || str_starts_with($dir, $prefix)) {
                if (! $preserve || $dir !== $normalized) {
                    unset($this->directories[$dir], $this->timestamps[$dir]);
                }
            }
        }

        if (! $preserve) {
            unset($this->directories[$normalized], $this->timestamps[$normalized]);
        }

        return true;
    }

    /** @param  array<int, mixed>|string|int  $depth */
    public function files($directory, $hidden = false, array|string|int $depth = 0) // @phpstan-ignore method.childParameterType
    {
        if (! $this->isFaked($directory)) {
            return parent::files($directory, $hidden, $depth);
        }

        $normalized = rtrim($directory, '/');
        $prefix = $normalized.'/';
        $results = [];

        foreach (array_keys($this->files) as $path) {
            if (! str_starts_with($path, $prefix)) {
                continue;
            }

            $relative = substr($path, strlen($prefix));

            if ($depth === 0 && str_contains($relative, '/')) {
                continue;
            }

            $results[] = new SplFileInfo($path, dirname($relative) === '.' ? '' : dirname($relative), $relative);
        }

        usort($results, fn (SplFileInfo $a, SplFileInfo $b) => strcmp($a->getFilename(), $b->getFilename()));

        return $results;
    }

    /**
     * @return SplFileInfo[]
     */
    public function allFiles($directory, $hidden = false)
    {
        if (! $this->isFaked($directory)) {
            return parent::allFiles($directory, $hidden);
        }

        return $this->files($directory, $hidden, []);
    }

    /**
     * @param  array<int, mixed>|string|int  $depth
     * @return array<int, string>
     */
    public function directories($directory, array|string|int $depth = 0) // @phpstan-ignore method.childParameterType
    {
        if (! $this->isFaked($directory)) {
            return parent::directories($directory, $depth);
        }

        $normalized = rtrim($directory, '/');
        $prefix = $normalized.'/';
        $results = [];

        foreach (array_keys($this->directories) as $dir) {
            if ($dir === $normalized || ! str_starts_with($dir, $prefix)) {
                continue;
            }

            $relative = substr($dir, strlen($prefix));

            if ($depth === 0 && str_contains($relative, '/')) {
                continue;
            }

            $results[] = $dir;
        }

        sort($results);

        return $results;
    }

    /**
     * In-memory `getRequire()` via restricted eval.
     *
     * Only supports the known `<?php return …;` file shape.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getRequire($path, array $data = []) // @phpstan-ignore method.childParameterType
    {
        if (! $this->isFaked($path)) {
            return parent::getRequire($path, $data);
        }

        if (! array_key_exists($path, $this->files)) {
            throw new FileNotFoundException("File does not exist at path {$path}.");
        }

        $contents = $this->files[$path];

        $code = $contents;

        if (str_starts_with($code, '<?php')) {
            $code = substr($code, 5);
        } else {
            throw new \RuntimeException("FilesystemFake::getRequire() only supports files starting with '<?php'. Got: ".substr($contents, 0, 50));
        }

        extract($data, EXTR_SKIP);

        return eval($code); // @phpstan-ignore ergebnis.noEval
    }

    private function globToRegex(string $pattern): string
    {
        $regex = '';

        for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
            $char = $pattern[$i];
            $regex .= match ($char) {
                '*' => '[^/]*',
                '?' => '[^/]',
                '.' => '\\.',
                '\\' => '\\\\',
                '/' => '\\/',
                '{' => '(',
                '}' => ')',
                ',' => '|',
                default => preg_quote($char, '#'),
            };
        }

        return '#^'.$regex.'$#';
    }
}
