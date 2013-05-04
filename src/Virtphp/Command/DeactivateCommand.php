<?php

/*
 * This file is part of VirtPHP.
 *
 * (c) Jordan Kasper <github @jakerella> 
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeactivateCommand extends Command
{

    /**
     * Function that defines command name and what
     * variables we are taking.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deactivate')
            ->setDescription('Deactives active environment.');
    }

    /*
     * Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Make sure we are in an active environment
        if ($this->isActiveEnv() === false) {
            $output->writeln('<bg=red>VirtPHP is not currently activated</bg=red>');
            return false;
        }

        // Process for deactivating active environment 

        $output->writeln('<bg=green>Deactivated</bg=green>'); // delete after setup
    }

    /** 
     * Function to make sure provided 
     * environment name is valid.
     *
     * @param string $virt_env
     */
    function isActiveEnv()
    {
        // Logic for validating name
        return true;
    }
}
