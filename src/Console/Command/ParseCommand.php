<?php

namespace Tempa\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('dir', InputArgument::REQUIRED, 'Path to directory to be parsed');
        $this->setName('file:parse')
             ->setDescription('Parse source directory for templates files replacing placeholders')
             ->setHelp(
                 <<<EOT
Iterate a given directory searching for template files.
This will replace found placeholders with a set up value.
EOT
             );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Awesome it wurks!</info>');
    }

}
