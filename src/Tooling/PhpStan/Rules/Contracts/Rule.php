<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Contracts;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @template TNodeType of \PhpParser\Node
 *
 * @extends \PHPStan\Rules\Rule<TNodeType>
 */
interface Rule extends \PHPStan\Rules\Rule
{
    /**
     * @param  TNodeType  $node
     */
    public function prepare(Node $node, Scope $scope): void;

    /**
     * @param  TNodeType  $node
     */
    public function handle(Node $node, Scope $scope): void;

    /**
     * @param  TNodeType  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool;
}
