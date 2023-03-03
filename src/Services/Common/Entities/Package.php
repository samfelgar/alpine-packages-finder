<?php

namespace Samfelgar\AlpinePackages\Services\Common\Entities;

final class Package
{
    public function __construct(
        public readonly string $name,
        public readonly Branch $branch = new Branch(),
        public readonly ?Repository $repository = null,
        public readonly ?Arch $arch = null,
        public readonly ?string $maintainer = null,
    ) {
    }
}