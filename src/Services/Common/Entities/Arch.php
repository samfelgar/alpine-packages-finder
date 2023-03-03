<?php

namespace Samfelgar\AlpinePackages\Services\Common\Entities;

enum Arch: string
{
    case X86_64 = 'x86_64';
    case X86 = 'x86';
    case Aarch65 = 'aarch64';
    case Armhf = 'armhf';
    case Ppc64le = 'ppc64le';
    case S390x = 's390x';
    case Armv7 = 'armv7';
    case Riscv64 = 'riscv64';
}
