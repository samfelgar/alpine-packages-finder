<?php

namespace Samfelgar\AlpinePackages\Services\Parser;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParser
{
    public function getResults(string $html): HtmlParserResult
    {
        $crawler = new Crawler($html);

        try {
            $crawler->filter('tbody .package a')->text();

            $version = $crawler->filter('tbody .version strong')->text();

            $repositories = $crawler->filter('tbody .repo a')->each(function (Crawler $crawler) {
                return $crawler->text();
            });

            return new HtmlParserResult(true, $version, \array_keys(\array_flip($repositories)));
        } catch (InvalidArgumentException) {
            return new HtmlParserResult(false, null, []);
        }
    }
}
