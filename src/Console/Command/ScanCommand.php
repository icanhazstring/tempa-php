<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tempa\Core\Options;
use Tempa\Core\Processor;
use Tempa\Core\Scan\ResultContainer;
use Tempa\Instrument\FileSystem\FileIterator;

/**
 * ScanCommand
 *
 * Command to execute scan process
 *
 * @package Tempa\Console\Command
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class ScanCommand extends AbstractCommand
{

    protected function configure()
    {
        parent::configure();
        $this->setName('file:scan')
             ->setDescription('Scan source directory for templates files')
             ->setHelp(
                 <<<EOT
Iterate a given directory searching for template files.
This will list all placeholders and template files.
EOT
             );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $input->getArgument('config');
        $configPath = stream_resolve_include_path($config);

        $scanDirectory = $input->getArgument('dir');
        $scanPath = stream_resolve_include_path($scanDirectory);

        // If config is empty, try reading it from current execution path
        if ($config === null) {
            $configPath = $configPath . DIRECTORY_SEPARATOR . 'tempa.json';
        }

        if (!is_readable($configPath)) {
            throw new \InvalidArgumentException("Config not readable: {$configPath}");
        }

        $io->title("Scanning for template files in: {$scanPath}");

        $options = new Options(json_decode(file_get_contents($configPath), true));
        $iterator = new FileIterator($scanPath, $options->fileExtensions);

        if (is_dir($scanPath)) {
            /** @var \SplFileInfo[]|\CallbackFilterIterator $result */
            $result = $iterator->iterate();
            $io->progressStart(count(iterator_to_array($result)));
        } else {
            $result = [new \SplFileInfo($scanPath)];
            $io->progressStart(1);
        }

        $processor = new Processor($options);

        // Collect scan result
        $fileResults = [];

        foreach ($result as $file) {
            $fileResults[] = $processor->scan(new \SplFileObject($file->getPathname()));
            $io->progressAdvance();
        }

        $io->progressFinish();

        $this->writeResults($io, $fileResults);
        
        return 0;
    }

    /**
     * @param SymfonyStyle      $io
     * @param ResultContainer[] $fileResults
     *
     * @return void
     */
    protected function writeResults(SymfonyStyle $io, array $fileResults)
    {
        $io->newLine();

        foreach ($fileResults as $fileResult) {
            $io->section($fileResult->getPathName());

            foreach ($fileResult as $file) {
                $io->writeln("Line {$file->lineNumber} : {$file->lineContent}");
            }
        }
    }
}
