<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules;

use Illuminate\Support\Facades\Date;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\Rector\Rules\ReplaceCarbonWithDateFacade;

class ReplaceCarbonWithDateFacadeTest extends TestCase
{
    use GetsFixtures;

    #[Test]
    public function it_has_rule_definition(): void
    {
        $rule = app(ReplaceCarbonWithDateFacade::class);

        $ruleDefinition = $rule->getRuleDefinition();

        $this->assertSame('Replace direct Carbon usage with the Date facade', $ruleDefinition->getDescription());
    }

    #[Test]
    public function it_replaces_carbon_static_call_with_date_facade(): void
    {
        $node = $this->getFirstNodeOfType(
            $this->getFixturePath('ClassWithCarbonStaticCall.php'),
            StaticCall::class,
        );

        $rule = app(ReplaceCarbonWithDateFacade::class);
        $result = $rule->refactor($node);

        $this->assertInstanceOf(StaticCall::class, $result);
        $this->assertInstanceOf(FullyQualified::class, $result->class);
        $this->assertSame(Date::class, $result->class->toString());
        $this->assertSame('now', $result->name->toString());
    }

    #[Test]
    public function it_replaces_carbon_new_with_date_create(): void
    {
        $node = $this->getFirstNodeOfType(
            $this->getFixturePath('ClassWithCarbonNew.php'),
            New_::class,
        );

        $rule = app(ReplaceCarbonWithDateFacade::class);
        $result = $rule->refactor($node);

        $this->assertInstanceOf(StaticCall::class, $result);
        $this->assertInstanceOf(FullyQualified::class, $result->class);
        $this->assertSame(Date::class, $result->class->toString());
        $this->assertSame('create', $result->name->toString());
        $this->assertCount(1, $result->args);
    }

    #[Test]
    public function it_replaces_carbon_immutable_static_call_with_date_facade(): void
    {
        $node = $this->getFirstNodeOfType(
            $this->getFixturePath('ClassWithCarbonImmutableStaticCall.php'),
            StaticCall::class,
        );

        $rule = app(ReplaceCarbonWithDateFacade::class);
        $result = $rule->refactor($node);

        $this->assertInstanceOf(StaticCall::class, $result);
        $this->assertInstanceOf(FullyQualified::class, $result->class);
        $this->assertSame(Date::class, $result->class->toString());
        $this->assertSame('now', $result->name->toString());
    }

    #[Test]
    public function it_does_not_replace_date_facade_calls(): void
    {
        $node = $this->getFirstNodeOfType(
            $this->getFixturePath('ClassWithDateFacade.php'),
            StaticCall::class,
        );

        $rule = app(ReplaceCarbonWithDateFacade::class);
        $result = $rule->refactor($node);

        $this->assertNull($result);
    }

    /**
     * @template T of Node
     *
     * @param  class-string<T>  $type
     * @return T
     */
    private function getFirstNodeOfType(string $path, string $type): Node
    {
        $code = file_get_contents($path);
        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $stmts = $parser->parse($code);

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $stmts = $traverser->traverse($stmts);

        $nodeFinder = new NodeFinder;
        $node = $nodeFinder->findFirstInstanceOf($stmts, $type);

        $this->assertNotNull($node, "No node of type {$type} found in {$path}");

        return $node;
    }
}
