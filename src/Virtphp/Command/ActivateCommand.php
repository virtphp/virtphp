<?php

/*
 * This file is part of virtPHP.
 *
 * (c) Jordan Kasper <github @jakerella>
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Virtphp\Virtphp;

class ActivateCommand extends Command
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('activate')
            ->setDescription(
                'Returns the activate file path to be sourced for '
                . 'the specified environment.'
            )
            ->addArgument(
                'env',
                InputArgument::REQUIRED,
                'Which environment do you want to activate'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->output = $output;
        $envName = $input->getArgument('env');
        $envFile = $this->getEnvFile();
        if (!$envName) {
            $output->writeln('<error>No environment name was provided.</error>');
            return false;
        }

        // Logic for getting the one environment and returning
        $activator = $this->getWorker('Activator', array(
            $output,
            $envName,
            $envFile
        ));
        if ($activator->execute()) {
            return true;
        }

        return false;
    }
}
