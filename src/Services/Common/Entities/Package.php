<?php

namespace Samfelgar\AlpinePackages\Services\Common\Entities;

final class Package
{
    private ?string $version = null;

    public function __construct(
        public readonly string $name,
        public readonly Branch $branch = new Branch(),
        public readonly ?Repository $repository = null,
        public readonly ?Arch $arch = null,
        public readonly ?string $maintainer = null,
    ) {}

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }
}
