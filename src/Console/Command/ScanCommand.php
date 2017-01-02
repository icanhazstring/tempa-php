<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tempa\Console\Output\ScanOutput;
use Tempa\Core\Options;
use Tempa\Core\Processor;
use Tempa\Core\Scan\ResultContainer;
use Tempa\Instrument\FileSystem\FileIterator;

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
        $config = $input->getOption('config');
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

        $output->writeln("<info>Scanning for template files in: {$scanPath}</info>");

        $config = file_get_contents($configPath);
        $options = new Options(json_decode($config, true));
        $iterator = new FileIterator($scanPath, $options->fileEndings);

        /** @var \SplFileInfo[]|\CallbackFilterIterator $result */
        $result = $iterator->iterate();
        $processor = new Processor($options);
        $progress = new ProgressBar($output, count(iterator_to_array($result)));

        // Collect scan result
        $fileResults = [];

        $progress->start();

        foreach ($result as $file) {
            $fileResults[] = $processor->scan(new \SplFileObject($file->getPathname()));
            $progress->advance();
        }

        $progress->finish();

        $this->writeResults($output, $fileResults);

    }

    /**
     * @param OutputInterface   $output
     * @param ResultContainer[] $fileResults
     *
     * @return void
     */
    protected function writeResults(OutputInterface $output, array $fileResults)
    {
        $output->write(PHP_EOL);

        foreach ($fileResults as $fileResult) {
            $output->writeln(PHP_EOL . "<info>{$fileResult->getPathName()}</info>");

            foreach ($fileResult as $file) {
                $output->writeln("Line {$file->lineNumber} : {$file->lineContent}");
            }

        }
    }
}
