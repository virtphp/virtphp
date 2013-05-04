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

class Clone extends Command
{

    /**
     * Function that defines command name and what
     * variables we are taking.
     */
    protected function configure()
    {
        $this
            ->setName('clone')
            ->setDescription('Create new virtphp from existing path.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'What is the name of your environment'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Where is the version of PHP you want to clone from?'
            );
    }

    /** 
     * Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env_name = $input->getArgument('name');
        $path = $input->getArgument('path');

        if (!$env_name && validName($env_name)) {
            $output->writeln('To create a clone, you must first name your clone.');
            return false;
        }

        if (!$path) {
            $output->writeln('To clone you must specify a location of a PHP binary to clonse from');
            return false;
        }

        // Validate the provided directory contains what we need
        $validateError = checkPath($path);
        if ($validateError) {
            $output->writeln('To clone you must specify a location of a PHP binary to clonse from');
            return false;
        }
        // Process the clone 
        $error = doClone($path);
        if ($error) {
            $output->writeln('To clone you must specify a location of a PHP binary to clonse from');
            return false;
        }
    }

    /** 
     * Function to check path for valid binary
     *
     * @param string $path
     */
    protected function checkPath($path)
    {
        // Logic to check directory before clone
    }

    /** 
     * Function to do the clone logic.
     *
     * @param string $path
     */
    protected function doClone($path)
    {
       // Logic for cloning directory 
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
