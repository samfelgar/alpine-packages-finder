<?php

declare(strict_types=1);

namespace Samfelgar\AlpinePackages\Services\Parser;

class HtmlParserResult
{
    /**
     * @param array<int, string> $repositories
     */
    public function __construct(
        public readonly bool $found,
        public readonly ?string $version,
        public readonly array $repositories = [],
    ) {
    }
}
