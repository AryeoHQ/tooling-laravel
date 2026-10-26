<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_tags_classmap_collectors(): void
    {
        $tagged = iterator_to_array(app()->tagged('tooling.classmap.collectors'));

        $this->assertCount(2, $tagged);
    }

    #[Test]
    public function it_listens_for_command_finished(): void
    {
        $this->assertTrue(Event::hasListeners(CommandFinished::class));
    }
}
