<?php

namespace Samfelgar\AlpinePackages\Console;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Samfelgar\AlpinePackages\Services\Common\Entities\Arch;
use Samfelgar\AlpinePackages\Services\Common\Entities\Branch;
use Samfelgar\AlpinePackages\Services\Common\Entities\Package;
use Samfelgar\AlpinePackages\Services\Common\Entities\Repository;
use Samfelgar\AlpinePackages\Services\Parser\FileParser;
use Samfelgar\AlpinePackages\Services\Parser\HtmlParser;
use Samfelgar\AlpinePackages\Services\Retriever\Retriever;
use Samfelgar\AlpinePackages\Services\Seeker\Options;
use Samfelgar\AlpinePackages\Services\Seeker\PackageSeeker;
use Samfelgar\AlpinePackages\Services\Seeker\Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AlpinePackagesSeeker extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('alpine-seeker')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'The file/directory path')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The package name you want to search for')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Filter by branch')
            ->addOption('repository', null, InputOption::VALUE_REQUIRED, 'Filter by repository')
            ->addOption('arch', null, InputOption::VALUE_REQUIRED, 'The system architecture')
            ->addOption('maintainer', null, InputOption::VALUE_REQUIRED, 'Filter by maintainer')
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'Number of simultaneous requests', default: 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');
        $packageName = $input->getOption('name');

        if ($path === null && $packageName === null) {
            $io->error('You must inform at least one option');
            return Command::FAILURE;
        }

        $seeker = $this->packageSeeker();

        $results = [];

        if ($path !== null) {
            try {
                \array_push($results, ...$this->handlePath($path, $input));
            } catch (InvalidArgumentException $exception) {
                $io->error($exception->getMessage());
                return Command::FAILURE;
            }
        }

        if ($packageName !== null) {
            $results[] = $seeker->byPackageName(new Package($packageName));
        }

        $io->table(
            ['Package name', 'Version', 'Found?', 'Message', 'Repositories'],
            \array_map(static fn(Result $result) => [
                $result->package->name,
                $result->package->getVersion() ?? '-',
                $result->found ? '<info>Yes</info>' : '<error>No</error>',
                $result->message ?? '-',
                \implode(', ', $result->repositories),
            ], $results)
        );

        return Command::SUCCESS;
    }

    /**
     * @return Result[]
     */
    private function handlePath(string $path, InputInterface $input): array
    {
        $seeker = $this->packageSeeker();
        $path = (new FileParser())->expandHomeDirectory($path);
        $options = $this->seekerOptions($input);

        if (\is_dir($path)) {
            return $seeker->byDirectoryPath($path, $options);
        }

        if (\is_file($path)) {
            return $seeker->byFilePath($path, $options);
        }

        throw new InvalidArgumentException('The path must point to a file or directory');
    }

    private function seekerOptions(InputInterface $input): Options
    {
        $repository = $input->getOption('repository');
        $arch = $input->getOption('arch');

        return new Options(
            new Branch($input->getOption('branch') ?? Branch::BRANCH_EDGE),
            $repository !== null ? Repository::tryFrom($repository) : null,
            $arch !== null ? Arch::tryFrom($arch) : null,
            $input->getOption('maintainer'),
            (int) $input->getOption('concurrency')
        );
    }

    private function packageSeeker(): PackageSeeker
    {
        $handler = HandlerStack::create();
        $handler->push(Middleware::log($this->logger, new MessageFormatter(MessageFormatter::SHORT)));

        $client = new Client(['handler' => $handler]);

        return new PackageSeeker(
            new Retriever($client),
            new HtmlParser(),
            new FileParser(),
            $this->logger
        );
    }
}
