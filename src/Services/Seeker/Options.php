<?php

namespace Samfelgar\AlpinePackages\Services\Seeker;

use Samfelgar\AlpinePackages\Services\Common\Entities\Arch;
use Samfelgar\AlpinePackages\Services\Common\Entities\Branch;
use Samfelgar\AlpinePackages\Services\Common\Entities\Repository;

final class Options
{
    public function __construct(
        public readonly Branch $branch = new Branch(),
        public readonly ?Repository $repository = null,
        public readonly ?Arch $arch = null,
        public readonly ?string $maintainer = null,
        public readonly int $concurrency = 10,
    ) {
    }
}
