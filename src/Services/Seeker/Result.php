<?php

namespace Samfelgar\AlpinePackages\Services\Seeker;

use Samfelgar\AlpinePackages\Services\Common\Entities\Package;

final class Result
{
    public function __construct(
        public readonly Package $package,
        public readonly bool $found,
        public readonly ?string $message,
        public readonly array $repositories = [],
    ) {
    }
}