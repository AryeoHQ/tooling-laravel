<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Enums;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
use Tooling\PHPStan\Rules\Enums\CaseMustBePascal;

class CaseMustBePascalTest extends TestCase
{
    private CaseMustBePascal $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CaseMustBePascal();
    }

    public function testValidPascalCaseEnumNames(): void
    {
        // Test traditional PascalCase names
        $this->assertPascalCase('Colonial', true);
        $this->assertPascalCase('CapeCod', true);
        $this->assertPascalCase('FooBar', true);
        $this->assertPascalCase('FooBarBaz', true);

        // Test single-letter "words" in PascalCase (the fix)
        $this->assertPascalCase('AFrame', true);
        $this->assertPascalCase('ABTest', true);
        $this->assertPascalCase('IOStream', true);
        $this->assertPascalCase('A', true);
        $this->assertPascalCase('AB', true);
        $this->assertPascalCase('ABC', true);
    }

    public function testInvalidEnumNames(): void
    {
        // Test non-PascalCase names
        $this->assertPascalCase('colonial', false);
        $this->assertPascalCase('capeCod', false);
        $this->assertPascalCase('COLONIAL', false);
        $this->assertPascalCase('CAPE_COD', false);
        $this->assertPascalCase('Cape_Cod', false);
        $this->assertPascalCase('cape_cod', false);
        $this->assertPascalCase('aFrame', false);
        $this->assertPascalCase('a_frame', false);
    }

    /**
     * Assert whether a given enum case name is considered PascalCase.
     */
    private function assertPascalCase(string $name, bool $expected): void
    {
        $enumCase = new EnumCase(
            new Identifier($name),
            null,
            []
        );

        $scope = $this->createMock(Scope::class);
        $classReflection = $this->createMock(ClassReflection::class);

        $classReflection->method('isEnum')->willReturn(true);
        $scope->method('getClassReflection')->willReturn($classReflection);

        $errors = $this->rule->processNode($enumCase, $scope);

        if ($expected) {
            $this->assertCount(
                0,
                $errors,
                sprintf('Expected "%s" to be valid PascalCase, but got errors', $name)
            );
        } else {
            $this->assertCount(
                1,
                $errors,
                sprintf('Expected "%s" to be invalid PascalCase, but got no errors', $name)
            );
        }
    }
}
