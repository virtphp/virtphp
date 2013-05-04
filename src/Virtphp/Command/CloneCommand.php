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

class CloneCommand extends Command
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

        if (!$env_name && $this->validName($env_name)) {
            $output->writeln('<bg=red>To create a clone, you must first name your clone.</bg=red>');
            return false;
        }

        if (!$path) {
            $output->writeln('<bg=red>We need a path to clone from.</bg=red>');
            return false;
        }

        // Validate the provided directory contains what we need
        $validateError = $this->checkPath($path);
        if ($validateError === false) {
            $output->writeln('<bg=red>Path provided is invalid or is missing required assets.</bg=red>');
            return false;
        }
        // Process the clone 
        $cloneError = $this->doClone($path);
        if (!$cloneError) {
            $output->writeln($cloneError);
            return false;
        }

        $output->writeln('<bg=green;options=bold>Congratulations, your new VirtPHP environment has been cloned!</bg=green;options=bold>');
    }

    /** 
     * Function to check path for valid binary
     *
     * @param string $path
     */
    protected function checkPath($path)
    {
        // Logic to check directory before clone
        if (is_dir($path) === false) {
            if (file_exists($path)) {
                echo 'Path provided is a file!
';
            }
            return false; 
        }

        return true;
    }

    /** 
     * Function to do the clone logic.
     *
     * @param string $path
     */
    protected function doClone($path)
    {
        // Logic for cloning directory 
        return true;
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
        return true;
    }
}
