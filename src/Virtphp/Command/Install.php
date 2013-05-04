<?php

/*
 * This file is part of VirtPHP.
 *
 * (c) Jordan Kasper <github @jakerella> 
 *     Jacques Woodcock <github @jwoodcock> 
 *     Ben Ramsey <github @ramsey>
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

class Install extends Command
{

    /**
     * Function that defines command name and what
     * variables we are taking.
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Create new virtphp from scratch.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'What is the name of your environment'
            );
    }

    /*
     * Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env_name = $input->getArgument('name');

        // Check to make sure environment name is valid
        if ($env_name && !validName($env_name)) {
            $output->writeln('The name provided is not valid.');
            return false;
        }
        // Check default locations for valid PHP
        //
        // Setup environment
    }

    /** 
     * Function to make sure provided 
     * environment name is valid.
     *
     * @param string $env_name
     */
    function validName($env_name)
    {
        // Logic for validating name
    }
}
