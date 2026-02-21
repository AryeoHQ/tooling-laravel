<?php

declare(strict_types=1);

namespace Tests\Tooling\Composer\Packages;

use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;
use Tooling\Composer\Packages\Package;

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
}
