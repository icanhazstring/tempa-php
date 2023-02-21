<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tempa\Console\Helper\ArgumentParser;
use Tempa\Core\Exception\SubstituteException;
use Tempa\Core\Options;
use Tempa\Core\Processor;
use Tempa\Instrument\FileSystem\FileIterator;

/**
 * SubstituteCommand
 *
 * Command for execution substitute process
 *
 * @package Tempa\Console\Command
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class SubstituteCommand extends AbstractCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('mapfile', 'f', InputOption::VALUE_OPTIONAL, '');
        $this->addArgument('map', InputOption::VALUE_OPTIONAL);

        $this->setName('file:substitute')
             ->setDescription('Parse source directory for templates files replacing placeholders')
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

        $mapFile = $input->getOption('mapfile');

        if ($mapFile) {
            $mapFilePath = stream_resolve_include_path($mapFile);
            $map = ArgumentParser::parseFile($mapFilePath);
        } else {
            $map = ArgumentParser::parseMapping($input->getArgument('map') ?: []);
        }

        $rootDirectory = $input->getArgument('dir');
        $rootPath = stream_resolve_include_path($rootDirectory);

        // If config is empty, try reading it from current execution path
        if ($config === null) {
            $configPath .= DIRECTORY_SEPARATOR . 'tempa.json';
        }

        if (!is_readable($configPath)) {
            throw new \InvalidArgumentException("Config not readable: {$configPath}");
        }

        $io->title("Processing template files in: {$rootPath}");

        $options = new Options(json_decode(file_get_contents($configPath), true));
        $iterator = new FileIterator($rootPath, $options->fileExtensions);

        if (is_dir($rootPath)) {
            /** @var \SplFileInfo[]|\CallbackFilterIterator $result */
            $result = $iterator->iterate();
            $io->progressStart(count(iterator_to_array($result)));
        } else {
            $result = [new \SplFileInfo($rootPath)];
            $io->progressStart(1);
        }

        $processor = new Processor($options);

        /** @var SubstituteException[] $fileErrors */
        $fileErrors = [];

        foreach ($result as $file) {
            if (!isset($fileErrors[$file->getPathname()])) {
                $fileErrors[$file->getPathname()] = [];
            }

            try {
                $processor->substitute(new \SplFileObject($file->getPathname()), $map);
            } catch (SubstituteException $e) {
                $fileErrors[$file->getPathname()][] = $e;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        foreach ($fileErrors as $file => $errors) {
            if (empty($errors)) {
                continue;
            }

            $io->section($file);

            foreach ($errors as $error) {
                $io->note($error->getMessage());
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
