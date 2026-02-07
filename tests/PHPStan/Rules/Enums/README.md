# PHPStan Enum PascalCase Rule Tests

This directory contains tests for the `CaseMustBePascal` PHPStan rule.

## Test Structure

### Unit Test
- **CaseMustBePascalTest.php**: PHPUnit test that directly tests the rule logic with various enum case names

### Fixtures
The `Fixtures` directory contains actual enum files that can be analyzed by PHPStan:
- **ValidPascalCaseEnum.php**: Contains enum cases that should pass PascalCase validation, including:
  - Traditional PascalCase: `Colonial`, `CapeCod`, `FooBar`
  - Single-letter "words": `AFrame`, `ABTest`, `IOStream`, `A`, `AB`, `ABC`
  
- **InvalidPascalCaseEnum.php**: Contains enum cases that should fail PascalCase validation

### PHPStan Configuration
- **phpstan.neon**: Configuration file to run PHPStan on the fixtures

## Running the Tests

### PHPUnit Test
```bash
vendor/bin/phpunit tests/PHPStan/Rules/Enums/CaseMustBePascalTest.php
```

### PHPStan Integration Test
Run PHPStan on the fixtures to verify the rule works correctly:
```bash
cd tests/PHPStan/Rules/Enums
vendor/bin/phpstan analyze -c phpstan.neon
```

Expected results:
- `ValidPascalCaseEnum.php`: 0 errors
- `InvalidPascalCaseEnum.php`: 8 errors (one per invalid case)

## What This Tests

The fix changes the regex pattern from `/^([A-Z][a-z0-9]+)+$/` to `/^([A-Z][a-z0-9]*)+$/`.

The key difference:
- **Before** (`+`): Required at least one lowercase letter/digit after each uppercase letter
  - ❌ `AFrame` failed (first word `A` has no trailing lowercase)
  
- **After** (`*`): Allows zero or more lowercase letters/digits after each uppercase letter
  - ✅ `AFrame` passes (single-letter words are valid)
  - ✅ `ABTest` passes
  - ✅ `IOStream` passes
  - ✅ Traditional PascalCase still works: `Colonial`, `CapeCod`
