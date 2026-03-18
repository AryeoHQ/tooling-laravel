<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

class PackageTest extends TestCase
{
    #[Test]
    public function it_returns_package_name_as_stringable(): void
    {
        $data = (object) ['name' => 'vendor/package'];
        $package = new Package($data);

        $this->assertInstanceOf(Stringable::class, $package->name);
        $this->assertSame('vendor/package', $package->name->toString());
    }

    #[Test]
    public function it_returns_package_version_as_stringable(): void
    {
        $data = (object) ['version' => '1.2.3'];
        $package = new Package($data);

        $this->assertInstanceOf(Stringable::class, $package->version);
        $this->assertSame('1.2.3', $package->version->toString());
    }

    #[Test]
    public function it_returns_package_description_as_stringable(): void
    {
        $data = (object) ['description' => 'A testing package'];
        $package = new Package($data);

        $this->assertInstanceOf(Stringable::class, $package->description);
        $this->assertSame('A testing package', $package->description->toString());
    }

    #[Test]
    public function it_returns_extra_as_object(): void
    {
        $extra = (object) ['laravel' => ['providers' => ['SomeProvider']]];
        $data = (object) ['extra' => $extra];
        $package = new Package($data);

        $this->assertIsObject($package->extra);
        $this->assertSame($extra, $package->extra);
    }

    #[Test]
    public function it_returns_default_stdclass_when_extra_is_missing(): void
    {
        $data = (object) ['name' => 'vendor/package'];
        $package = new Package($data);

        $this->assertInstanceOf(stdClass::class, $package->extra);
        $this->assertEquals(new stdClass, $package->extra);
    }

    #[Test]
    public function it_returns_null_for_missing_properties(): void
    {
        $data = (object) ['name' => 'vendor/package'];
        $package = new Package($data);

        $this->assertNull($package->version);
        $this->assertNull($package->description);
    }

    #[Test]
    public function it_returns_autoload_as_object(): void
    {
        $autoload = (object) ['psr-4' => (object) ['App\\' => 'src/']];
        $data = (object) ['autoload' => $autoload];
        $package = new Package($data);

        $this->assertIsObject($package->autoload);
        $this->assertSame($autoload, $package->autoload);
    }

    #[Test]
    public function it_returns_default_stdclass_when_autoload_is_missing(): void
    {
        $data = (object) ['name' => 'vendor/package'];
        $package = new Package($data);

        $this->assertInstanceOf(stdClass::class, $package->autoload);
        $this->assertEquals(new stdClass, $package->autoload);
    }

    #[Test]
    public function it_exposes_psr4_mappings_from_autoload(): void
    {
        $data = (object) [
            'autoload' => (object) [
                'psr-4' => (object) [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                ],
            ],
        ];
        $package = new Package($data);

        $this->assertObjectHasProperty('psr-4', $package->autoload);
        $this->assertSame('app/', $package->autoload->{'psr-4'}->{'App\\'});
    }

    #[Test]
    public function it_handles_complete_package_data(): void
    {
        $data = (object) [
            'name' => 'laravel/framework',
            'version' => '11.0.0',
            'description' => 'The Laravel Framework',
            'extra' => (object) [
                'laravel' => (object) [
                    'providers' => ['Illuminate\Foundation\Providers\FoundationServiceProvider'],
                ],
            ],
            'autoload' => (object) [
                'psr-4' => (object) ['Illuminate\\' => 'src/Illuminate/'],
            ],
        ];
        $package = new Package($data);

        $this->assertInstanceOf(Stringable::class, $package->name);
        $this->assertSame('laravel/framework', $package->name->toString());
        $this->assertSame('11.0.0', $package->version->toString());
        $this->assertSame('The Laravel Framework', $package->description->toString());
        $this->assertIsObject($package->extra);
        $this->assertIsObject($package->autoload);
        $this->assertObjectHasProperty('psr-4', $package->autoload);
    }

    #[Test]
    public function it_handles_nested_data_access(): void
    {
        $data = (object) [
            'name' => 'vendor/package',
            'extra' => (object) [
                'branch-alias' => (object) [
                    'dev-main' => '1.0-dev',
                ],
            ],
        ];
        $package = new Package($data);

        $this->assertIsObject($package->extra);
        $this->assertObjectHasProperty('branch-alias', $package->extra);
    }

    #[Test]
    public function it_returns_psr4_mappings_with_string_values(): void
    {
        $data = json_decode(json_encode([
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                ],
            ],
        ]));

        $package = new Package($data);

        $this->assertCount(2, $package->psr4Mappings);
        $this->assertInstanceOf(Psr4Mapping::class, $package->psr4Mappings->get(0));
        $this->assertSame('\\App\\', $package->psr4Mappings->get(0)->prefix->toString());
        $this->assertSame('app/', $package->psr4Mappings->get(0)->path->toString());
        $this->assertSame('\\Database\\Factories\\', $package->psr4Mappings->get(1)->prefix->toString());
        $this->assertSame('database/factories/', $package->psr4Mappings->get(1)->path->toString());
    }

    #[Test]
    public function it_expands_array_psr4_values_into_multiple_rows(): void
    {
        $data = json_decode(json_encode([
            'autoload' => [
                'psr-4' => [
                    'App\\' => ['app/', 'app-extra/'],
                ],
            ],
        ]));

        $package = new Package($data);

        $this->assertCount(2, $package->psr4Mappings);
        $this->assertSame('\\App\\', $package->psr4Mappings->get(0)->prefix->toString());
        $this->assertSame('app/', $package->psr4Mappings->get(0)->path->toString());
        $this->assertSame('\\App\\', $package->psr4Mappings->get(1)->prefix->toString());
        $this->assertSame('app-extra/', $package->psr4Mappings->get(1)->path->toString());
    }

    #[Test]
    public function it_returns_empty_psr4_mappings_when_no_psr4_section_exists(): void
    {
        $data = (object) ['name' => 'vendor/package'];
        $package = new Package($data);

        $this->assertCount(0, $package->psr4Mappings);
    }

    #[Test]
    public function it_merges_autoload_and_autoload_dev_psr4_mappings(): void
    {
        $data = json_decode(json_encode([
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Tests\\' => 'tests/',
                ],
            ],
        ]));

        $package = new Package($data);

        $this->assertCount(2, $package->psr4Mappings);
        $this->assertSame('\\App\\', $package->psr4Mappings->get(0)->prefix->toString());
        $this->assertSame('app/', $package->psr4Mappings->get(0)->path->toString());
        $this->assertSame('\\Tests\\', $package->psr4Mappings->get(1)->prefix->toString());
        $this->assertSame('tests/', $package->psr4Mappings->get(1)->path->toString());
    }
}
