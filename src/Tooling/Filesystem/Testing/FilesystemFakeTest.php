<?php

declare(strict_types=1);

namespace Tooling\Filesystem\Testing;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

#[CoversClass(FilesystemFake::class)]
class FilesystemFakeTest extends TestCase
{
    private const string BASE = '/fake';

    private function fake(): FilesystemFake
    {
        return new FilesystemFake(self::BASE.'/*');
    }

    #[Test]
    public function it_accepts_a_string_path(): void
    {
        $fake = new FilesystemFake('/fake/*');

        $fake->put('/fake/file.txt', 'hello');

        $this->assertSame('hello', $fake->get('/fake/file.txt'));
    }

    #[Test]
    public function it_accepts_an_array_of_paths(): void
    {
        $fake = new FilesystemFake(['/a/*', '/b/*']);

        $fake->put('/a/file.txt', 'a');
        $fake->put('/b/file.txt', 'b');

        $this->assertSame('a', $fake->get('/a/file.txt'));
        $this->assertSame('b', $fake->get('/b/file.txt'));
    }

    #[Test]
    public function it_deduplicates_faked_paths(): void
    {
        $fake = new FilesystemFake('/fake/*');
        $fake->addFakedPaths('/fake/*');
        $fake->addFakedPaths(['/fake/*', '/other/*']);

        // No error — just confirms no crash on duplicates.
        $fake->put('/fake/file.txt', 'ok');
        $this->assertSame('ok', $fake->get('/fake/file.txt'));
    }

    #[Test]
    public function it_returns_itself_from_add_faked_paths(): void
    {
        $fake = new FilesystemFake;

        $this->assertSame($fake, $fake->addFakedPaths('/fake/*'));
    }

    #[Test]
    public function it_reports_file_exists_after_put(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'content');

        $this->assertTrue($fake->exists(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_reports_file_does_not_exist_before_put(): void
    {
        $fake = $this->fake();

        $this->assertFalse($fake->exists(self::BASE.'/missing.txt'));
    }

    #[Test]
    public function it_reports_directory_exists(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');

        $this->assertTrue($fake->exists(self::BASE.'/dir'));
    }

    #[Test]
    public function it_stores_and_retrieves_file_contents(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'hello world');

        $this->assertSame('hello world', $fake->get(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_throws_file_not_found_for_missing_file(): void
    {
        $fake = $this->fake();

        $this->expectException(FileNotFoundException::class);

        $fake->get(self::BASE.'/missing.txt');
    }

    #[Test]
    public function it_returns_bytes_written_from_put(): void
    {
        $fake = $this->fake();

        $this->assertSame(5, $fake->put(self::BASE.'/file.txt', 'hello'));
    }

    #[Test]
    public function it_reads_json_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/data.json', json_encode(['key' => 'value']));

        $this->assertSame(['key' => 'value'], $fake->json(self::BASE.'/data.json'));
    }

    #[Test]
    public function it_appends_to_existing_file(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'hello');
        $fake->append(self::BASE.'/file.txt', ' world');

        $this->assertSame('hello world', $fake->get(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_appends_to_nonexistent_file(): void
    {
        $fake = $this->fake();
        $fake->append(self::BASE.'/new.txt', 'data');

        $this->assertSame('data', $fake->get(self::BASE.'/new.txt'));
    }

    #[Test]
    public function it_returns_bytes_appended(): void
    {
        $fake = $this->fake();

        $this->assertSame(4, $fake->append(self::BASE.'/file.txt', 'data'));
    }

    #[Test]
    public function it_deletes_a_faked_file(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'content');

        $fake->delete(self::BASE.'/file.txt');

        $this->assertFalse($fake->exists(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_deletes_multiple_faked_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/a.txt', 'a');
        $fake->put(self::BASE.'/b.txt', 'b');

        $fake->delete([self::BASE.'/a.txt', self::BASE.'/b.txt']);

        $this->assertFalse($fake->exists(self::BASE.'/a.txt'));
        $this->assertFalse($fake->exists(self::BASE.'/b.txt'));
    }

    #[Test]
    public function it_copies_a_faked_file(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/source.txt', 'data');

        $fake->copy(self::BASE.'/source.txt', self::BASE.'/target.txt');

        $this->assertSame('data', $fake->get(self::BASE.'/target.txt'));
        $this->assertSame('data', $fake->get(self::BASE.'/source.txt'));
    }

    #[Test]
    public function it_returns_false_when_copying_nonexistent_source(): void
    {
        $fake = $this->fake();

        $this->assertFalse($fake->copy(self::BASE.'/missing.txt', self::BASE.'/target.txt'));
    }

    #[Test]
    public function it_moves_a_faked_file(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/source.txt', 'data');

        $fake->move(self::BASE.'/source.txt', self::BASE.'/target.txt');

        $this->assertSame('data', $fake->get(self::BASE.'/target.txt'));
        $this->assertFalse($fake->exists(self::BASE.'/source.txt'));
    }

    #[Test]
    public function it_returns_false_when_moving_nonexistent_source(): void
    {
        $fake = $this->fake();

        $this->assertFalse($fake->move(self::BASE.'/missing.txt', self::BASE.'/target.txt'));
    }

    #[Test]
    public function it_is_a_noop_for_chmod_on_faked_path(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'data');

        $this->assertSame(0755, $fake->chmod(self::BASE.'/file.txt', 0755));
    }

    #[Test]
    public function it_identifies_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'content');

        $this->assertTrue($fake->isFile(self::BASE.'/file.txt'));
        $this->assertFalse($fake->isFile(self::BASE.'/missing.txt'));
    }

    #[Test]
    public function it_identifies_directories(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');

        $this->assertTrue($fake->isDirectory(self::BASE.'/dir'));
        $this->assertFalse($fake->isDirectory(self::BASE.'/missing'));
    }

    #[Test]
    public function it_creates_parent_directories_implicitly_on_put(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/a/b/c/file.txt', 'content');

        $this->assertTrue($fake->isDirectory(self::BASE.'/a/b/c'));
        $this->assertTrue($fake->isDirectory(self::BASE.'/a/b'));
        $this->assertTrue($fake->isDirectory(self::BASE.'/a'));
    }

    #[Test]
    public function it_detects_empty_directory(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/empty');

        $this->assertTrue($fake->isEmptyDirectory(self::BASE.'/empty'));
    }

    #[Test]
    public function it_detects_non_empty_directory_with_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/file.txt', 'content');

        $this->assertFalse($fake->isEmptyDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_detects_non_empty_directory_with_subdirectories(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');
        $fake->makeDirectory(self::BASE.'/dir/sub');

        $this->assertFalse($fake->isEmptyDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_returns_false_for_nonexistent_empty_directory_check(): void
    {
        $fake = $this->fake();

        $this->assertFalse($fake->isEmptyDirectory(self::BASE.'/nonexistent'));
    }

    #[Test]
    public function it_tracks_file_timestamps(): void
    {
        Date::setTestNow(now());
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'content');

        $this->assertSame(now()->timestamp, $fake->lastModified(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_tracks_directory_timestamps(): void
    {
        Date::setTestNow(now());
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');

        $this->assertSame(now()->timestamp, $fake->lastModified(self::BASE.'/dir'));
    }

    #[Test]
    public function it_throws_for_missing_path_last_modified(): void
    {
        $fake = $this->fake();

        $this->expectException(FileNotFoundException::class);

        $fake->lastModified(self::BASE.'/missing.txt');
    }

    #[Test]
    public function it_updates_timestamp_on_overwrite(): void
    {
        $fake = $this->fake();

        Date::setTestNow(now());
        $fake->put(self::BASE.'/file.txt', 'original');
        $originalTimestamp = $fake->lastModified(self::BASE.'/file.txt');

        Date::setTestNow(now()->addMinute());
        $fake->put(self::BASE.'/file.txt', 'updated');

        $this->assertGreaterThan($originalTimestamp, $fake->lastModified(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_updates_parent_directory_timestamp_on_put(): void
    {
        $fake = $this->fake();

        Date::setTestNow(now());
        $fake->put(self::BASE.'/dir/file1.txt', 'first');
        $originalTimestamp = $fake->lastModified(self::BASE.'/dir');

        Date::setTestNow(now()->addMinute());
        $fake->put(self::BASE.'/dir/file2.txt', 'second');

        $this->assertGreaterThan($originalTimestamp, $fake->lastModified(self::BASE.'/dir'));
    }

    #[Test]
    public function it_globs_faked_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/a.txt', 'a');
        $fake->put(self::BASE.'/b.txt', 'b');
        $fake->put(self::BASE.'/c.php', 'c');

        $result = $fake->glob(self::BASE.'/*.txt');

        $this->assertSame([self::BASE.'/a.txt', self::BASE.'/b.txt'], $result);
    }

    #[Test]
    public function it_globs_with_brace_expansion(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/a.txt', 'a');
        $fake->put(self::BASE.'/b.php', 'b');
        $fake->put(self::BASE.'/c.md', 'c');

        $result = $fake->glob(self::BASE.'/*.{txt,php}');

        $this->assertSame([self::BASE.'/a.txt', self::BASE.'/b.php'], $result);
    }

    #[Test]
    public function it_makes_a_directory(): void
    {
        $fake = $this->fake();

        $this->assertTrue($fake->makeDirectory(self::BASE.'/dir'));
        $this->assertTrue($fake->isDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_creates_parent_directories_when_making_directory(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/a/b/c');

        $this->assertTrue($fake->isDirectory(self::BASE.'/a/b'));
        $this->assertTrue($fake->isDirectory(self::BASE.'/a'));
    }

    #[Test]
    public function it_ensures_directory_exists_when_missing(): void
    {
        $fake = $this->fake();

        $fake->ensureDirectoryExists(self::BASE.'/dir');

        $this->assertTrue($fake->isDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_does_not_error_when_ensuring_existing_directory(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');

        $fake->ensureDirectoryExists(self::BASE.'/dir');

        $this->assertTrue($fake->isDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_deletes_a_directory_and_its_contents(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/file.txt', 'data');
        $fake->makeDirectory(self::BASE.'/dir/sub');

        $fake->deleteDirectory(self::BASE.'/dir');

        $this->assertFalse($fake->exists(self::BASE.'/dir'));
        $this->assertFalse($fake->exists(self::BASE.'/dir/file.txt'));
        $this->assertFalse($fake->isDirectory(self::BASE.'/dir/sub'));
    }

    #[Test]
    public function it_preserves_directory_when_deleting_with_preserve(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/file.txt', 'data');

        $fake->deleteDirectory(self::BASE.'/dir', preserve: true);

        $this->assertTrue($fake->isDirectory(self::BASE.'/dir'));
        $this->assertFalse($fake->exists(self::BASE.'/dir/file.txt'));
    }

    #[Test]
    public function it_lists_files_in_a_directory(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/a.txt', 'a');
        $fake->put(self::BASE.'/dir/b.txt', 'b');
        $fake->put(self::BASE.'/dir/sub/c.txt', 'c');

        $files = $fake->files(self::BASE.'/dir');

        $this->assertCount(2, $files);
        $this->assertContainsOnlyInstancesOf(SplFileInfo::class, $files);
        $filenames = array_map(fn (SplFileInfo $f) => $f->getFilename(), $files);
        $this->assertSame(['a.txt', 'b.txt'], $filenames);
    }

    #[Test]
    public function it_lists_all_files_recursively(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/a.txt', 'a');
        $fake->put(self::BASE.'/dir/sub/b.txt', 'b');

        $files = $fake->allFiles(self::BASE.'/dir');

        $this->assertCount(2, $files);
        $filenames = array_map(fn (SplFileInfo $f) => $f->getFilename(), $files);
        sort($filenames);
        $this->assertSame(['a.txt', 'b.txt'], $filenames);
    }

    #[Test]
    public function it_lists_directories(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir/a');
        $fake->makeDirectory(self::BASE.'/dir/b');
        $fake->makeDirectory(self::BASE.'/dir/a/nested');

        $dirs = $fake->directories(self::BASE.'/dir');

        $this->assertSame([self::BASE.'/dir/a', self::BASE.'/dir/b'], $dirs);
    }

    #[Test]
    public function it_returns_empty_directories_list_for_no_subdirectories(): void
    {
        $fake = $this->fake();
        $fake->makeDirectory(self::BASE.'/dir');

        $this->assertSame([], $fake->directories(self::BASE.'/dir'));
    }

    #[Test]
    public function it_evaluates_php_return_files(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/config.php', '<?php return ["key" => "value"];');

        $result = $fake->getRequire(self::BASE.'/config.php');

        $this->assertSame(['key' => 'value'], $result);
    }

    #[Test]
    public function it_throws_for_missing_file_on_get_require(): void
    {
        $fake = $this->fake();

        $this->expectException(FileNotFoundException::class);

        $fake->getRequire(self::BASE.'/missing.php');
    }

    #[Test]
    public function it_throws_for_non_php_file_on_get_require(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'not php');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('only supports files starting with');

        $fake->getRequire(self::BASE.'/file.txt');
    }

    #[Test]
    public function it_deletes_nonexistent_faked_path_without_error(): void
    {
        $fake = $this->fake();

        $fake->delete(self::BASE.'/nonexistent.txt');

        $this->assertFalse($fake->exists(self::BASE.'/nonexistent.txt'));
    }

    #[Test]
    public function it_returns_empty_string_for_chmod_without_mode(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/file.txt', 'data');

        $this->assertSame('', $fake->chmod(self::BASE.'/file.txt'));
    }

    #[Test]
    public function it_evaluates_php_return_files_with_data_extraction(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/config.php', '<?php return ["greeting" => $name];');

        $result = $fake->getRequire(self::BASE.'/config.php', ['name' => 'world']);

        $this->assertSame(['greeting' => 'world'], $result);
    }

    #[Test]
    public function it_returns_true_from_delete_directory(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/file.txt', 'data');

        $this->assertTrue($fake->deleteDirectory(self::BASE.'/dir'));
    }

    #[Test]
    public function it_returns_true_from_delete_directory_with_preserve(): void
    {
        $fake = $this->fake();
        $fake->put(self::BASE.'/dir/file.txt', 'data');

        $this->assertTrue($fake->deleteDirectory(self::BASE.'/dir', preserve: true));
    }
}
