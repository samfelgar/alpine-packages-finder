<?php

namespace Samfelgar\AlpinePackages\Services\Seeker;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Samfelgar\AlpinePackages\Services\Common\Entities\Package;
use Samfelgar\AlpinePackages\Services\Parser\FileParser;
use Samfelgar\AlpinePackages\Services\Parser\HtmlParser;
use Samfelgar\AlpinePackages\Services\Retriever\Retriever;
use Throwable;

class PackageSeeker
{
    public function __construct(
        private readonly Retriever $retriever,
        private readonly HtmlParser $htmlParser,
        private readonly FileParser $fileParser,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function byPackageName(Package $package): Result
    {
        try {
            $response = $this->retriever->retrieve($package);
        } catch (GuzzleException $e) {
            return new Result($package, false, $e->getMessage());
        }

        $result = $this->htmlParser->hasResults($response);
        return new Result($package, $result, null);
    }

    /**
     * @return Result[]
     */
    public function byFilePath(string $path, Options $options): array
    {
        $packages = [];

        foreach ($this->fileParser->parse($path) as $packageName) {
            $this->logger->debug('Discovered package: ' . $packageName);
            $packages[$packageName] = new Package(
                $packageName,
                $options->branch,
                $options->repository,
                $options->arch,
                $options->maintainer
            );
        }

        return $this->handleMultiplePackages($packages, $options->concurrency);
    }

    /**
     * @return Result[]
     */
    public function byDirectoryPath(string $path, Options $options): array
    {
        $packages = [];

        foreach ($this->fileParser->parseDirectory($path) as $packageName) {
            $this->logger->debug('Discovered package: ' . $packageName);
            $packages[$packageName] = new Package(
                $packageName,
                $options->branch,
                $options->repository,
                $options->arch,
                $options->maintainer
            );
        }

        return $this->handleMultiplePackages($packages, $options->concurrency);
    }

    /**
     * @return Result[]
     */
    private function handleMultiplePackages(array $packages, int $concurrency = 10): array
    {
        $results = [];

        $onFulfilled = function (string $packageName, string $html) use ($packages, &$results) {
            $results[] = new Result($packages[$packageName], $this->htmlParser->hasResults($html), null);
        };

        $onRejected = function (string $packageName, Throwable $exception) use ($packages, &$results) {
            $results[] = new Result($packages[$packageName], false, $exception->getMessage());
        };

        $this->retriever->retrieveMultiple($packages, $onFulfilled, $onRejected, $concurrency);
        return $results;
    }
}
