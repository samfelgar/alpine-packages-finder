<?php

namespace Samfelgar\AlpinePackages\Services\Common\Entities;

enum Repository: string
{
    case Community = 'community';
    case Main = 'main';
    case Testing = 'testing';
}
