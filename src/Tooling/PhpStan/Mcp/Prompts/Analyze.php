<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class Analyze extends Prompt
{
    protected string $name = 'tooling_phpstan_analyze';

    protected string $description = 'Analyzes code quality using PHPStan static analysis. Identifies type errors, undefined variables/methods, deprecated code, and Laravel-specific issues.';

    public function handle(Request $request): Response
    {
        return Response::text(<<<'TEXT'
        Analyze the codebase using the tooling_phpstan tool to identify code quality issues, type errors, and potential bugs.

        The tool will return structured JSON with:
        - Error counts and file statistics (totals)
        - Detailed errors per file with line numbers and messages (files)
        - Any configuration or runtime errors (errors)

        Your task:
        1. Run the tooling_phpstan tool
        2. Summarize the overall code quality status (total errors, files affected)
        3. Explain each error found with context about what it means and why it matters
        4. Provide specific, actionable recommendations for fixing the issues
        5. If appropriate, suggest using tooling_rector or tooling_pint tools for automated fixes
        6. Prioritize the most critical issues that should be addressed first into a TODO list
        TEXT);
    }
}
