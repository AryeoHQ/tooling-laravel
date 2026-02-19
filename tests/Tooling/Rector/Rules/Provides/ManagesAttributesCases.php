<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules\Provides;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\SomeAttribute;

trait ManagesAttributesCases
{
    #[Test]
    public function it_adds_an_attribute_to_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->hasAttribute($node, SomeAttribute::class));

        $this->addAttribute($node, SomeAttribute::class);

        $this->assertTrue($this->hasAttribute($node, SomeAttribute::class));
    }

    #[Test]
    public function it_does_not_add_an_attribute_that_already_exists(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithAttribute.php'));

        $attrGroupCount = count($node->attrGroups);

        $this->addAttribute($node, SomeAttribute::class);

        $this->assertCount($attrGroupCount, $node->attrGroups);
    }

    #[Test]
    public function it_removes_an_attribute_from_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithAttribute.php'));

        $this->assertTrue($this->hasAttribute($node, SomeAttribute::class));

        $this->removeAttribute($node, SomeAttribute::class);

        $this->assertFalse($this->hasAttribute($node, SomeAttribute::class));
    }

    #[Test]
    public function it_does_not_modify_a_class_when_removing_an_attribute_it_does_not_have(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $attrGroupCount = count($node->attrGroups);

        $this->removeAttribute($node, SomeAttribute::class);

        $this->assertCount($attrGroupCount, $node->attrGroups);
    }
}
