<?php

namespace Samfelgar\AlpinePackages\Services\Retriever;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Samfelgar\AlpinePackages\Services\Common\Entities\Package;

class Retriever
{
    public final const ALPINE_URL = 'https://pkgs.alpinelinux.org/packages';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function retrieve(Package $package): string
    {
        $request = $this->request($this->uri());
        $response = $this->client->send($request, [
            'query' => $this->queryParams($package),
        ]);

        return (string) $response->getBody();
    }

    /**
     * @param Package[] $packages
     * @param callable $onFulfilled Callback called when a request is fulfilled. It will receive as arguments
     *                              the package name and the response body (as string).
     * @param callable $onRejected Callback called when a request is rejected. It will receive the package name
     *                             and the throwed GuzzleHttp\Exception\RequestException.
     */
    public function retrieveMultiple(
        array $packages,
        callable $onFulfilled,
        callable $onRejected,
        int $concurrency = 10
    ): void {
        $requests = function (array $packages): Generator {
            $uri = $this->uri();

            /** @var Package $package */
            foreach ($packages as $package) {
                $query = $this->queryParams($package);
                $uriWithQuery = $uri->withQuery(http_build_query($query));
                yield $package->name => $this->request($uriWithQuery);
            }
        };

        $pool = new Pool($this->client, $requests($packages), [
            'concurrency' => $concurrency,
            'fulfilled' => function (ResponseInterface $response, string $packageName) use ($onFulfilled) {
                $onFulfilled($packageName, (string) $response->getBody());
            },
            'rejected' => function (RequestException $exception, string $packageName) use ($onRejected) {
                $onRejected($packageName, $exception);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    private function request(UriInterface $uri): RequestInterface
    {
        return new Request('get', $uri);
    }

    private function uri(): UriInterface
    {
        return Utils::uriFor(self::ALPINE_URL);
    }

    private function queryParams(Package $package): array
    {
        return [
            'name' => $package->name,
            'branch' => $package->branch->value,
            'repo' => $package->repository?->value ?? '',
            'arch' => $package->arch?->value ?? '',
            'maintainer' => $package->maintainer ?? '',
        ];
    }
}
