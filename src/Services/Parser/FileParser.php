<?php

namespace Samfelgar\AlpinePackages\Services\Parser;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Finder\Finder;

class FileParser
{
    public function parse(string $path): array
    {
        if (!\is_file($path) || !\is_readable($path)) {
            throw new InvalidArgumentException('You must inform a readable file.');
        }

        $handle = \fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Error while opening the provided path');
        }

        $packages = [];

        do {
            $line = \trim(\fgets($handle));

            if (\strlen($line) === 0) {
                continue;
            }

            $packages[] = $line;
        } while (!\feof($handle));

        \fclose($handle);

        return $packages;
    }

    /**
     * @param string $path Path to the directory containing the files to be parsed
     */
    public function parseDirectory(string $path): array
    {
        $path = $this->expandHomeDirectory($path);

        if (!is_dir($path)) {
            throw new InvalidArgumentException('You must inform a directory path');
        }

        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->ignoreDotFiles(true);

        if (!$finder->hasResults()) {
            throw new InvalidArgumentException('The informed directory is empty');
        }

        $packages = [];

        foreach ($finder as $file) {
            array_push($packages, ...$this->parse($file->getRealPath()));
        }

        return $packages;
    }

    public function expandHomeDirectory(string $path): string
    {
        if (!str_starts_with($path, '~')) {
            return $path;
        }

        return str_replace('~', getenv('HOME'), $path);
    }
}
