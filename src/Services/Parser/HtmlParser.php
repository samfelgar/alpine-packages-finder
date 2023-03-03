<?php

namespace Samfelgar\AlpinePackages\Services\Parser;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParser
{
    public function hasResults(string $html): bool
    {
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.package > a')->text();
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
