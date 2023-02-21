<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tempa\Core\Options;
use Tempa\Core\Processor;
use Tempa\Core\Scan\ResultContainer;
use Tempa\Instrument\FileSystem\FileIterator;

class InteractiveCommand extends AbstractCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->setName('file:interactive')
             ->setDescription('Scan source directory for templates files and interactivly replace them.')
             ->setHelp(
                 <<<EOT
Iterate a given directory searching for template files.
This will replace found placeholders with a set up value.
EOT
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = $input->getArgument('config');
        $configPath = stream_resolve_include_path($config);

        $scanDirectory = $input->getArgument('dir');
        $scanPath = stream_resolve_include_path($scanDirectory);

        // If config is empty, try reading it from current execution path
        if ($config === null) {
            $configPath .= DIRECTORY_SEPARATOR . 'tempa.json';
        }

        if (!is_readable($configPath)) {
            throw new \InvalidArgumentException("Config not readable: {$configPath}");
        }

        $io->title("Interactive substitution for template files in: {$scanPath}");

        $options = new Options(json_decode(file_get_contents($configPath), true));
        $iterator = new FileIterator($scanPath, $options->fileExtensions);

        if (is_dir($scanPath)) {
            /** @var \SplFileInfo[]|\CallbackFilterIterator $result */
            $result = $iterator->iterate();
        } else {
            $result = [new \SplFileInfo($scanPath)];
        }

        $processor = new Processor($options);
        $scanResult = [];
        $map = [];

        foreach ($result as $file) {
            $scanResult[] = $processor->scan(new \SplFileObject($file->getPathname()))->getItems();
        }

        $scanResult = array_merge(...$scanResult);

        $io->section('Found ' . count($scanResult) . ' substitutes');

        foreach ($scanResult as $substitute) {
            $map[$substitute->name] = $io->ask($substitute->name);
        }

        $io->section('Filling out substitutes');

        $io->progressStart(count($scanResult));

        foreach ($result as $file) {
            $processor->substitute(new \SplFileObject($file->getPathname()), $map);
            $io->progressAdvance();
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }
}
