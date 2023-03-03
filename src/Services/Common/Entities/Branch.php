<?php

namespace Samfelgar\AlpinePackages\Services\Common\Entities;

use InvalidArgumentException;

final class Branch
{
    public final const BRANCH_EDGE = 'edge';

    public readonly string $value;

    public function __construct(string $branch = self::BRANCH_EDGE)
    {
        $this->value = $this->normalizeBranch($branch);
    }

    private function normalizeBranch(string $branch): string
    {
        $branch = strtolower($branch);

        if ($branch === self::BRANCH_EDGE) {
            return $branch;
        }

        $this->validateBranchFormat($branch);

        if (!str_starts_with($branch, 'v')) {
            return 'v' . $branch;
        }

        return $branch;
    }

    private function validateBranchFormat(string $branch): void
    {
        $matchResult = preg_match('/^v?\d+(?:\.\d+)*$/', $branch);

        if ($matchResult === false || $matchResult === 0) {
            throw new InvalidArgumentException('Invalid branch');
        }
    }
}