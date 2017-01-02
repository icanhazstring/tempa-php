<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Config json with options required for parsing');
        $this->addArgument('dir', InputArgument::REQUIRED, 'Path to directory to be parsed');
    }
}
