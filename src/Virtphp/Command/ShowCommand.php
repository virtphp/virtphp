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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Virtphp\Virtphp;

class ShowCommand extends Command
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName("show")
            ->setDescription("Show a list of all the current environments.")
            ->addOption(
                "env",
                null,
                InputOption::VALUE_REQUIRED,
                "Which environment do you need to fix.",
                null
            )
            ->addOption(
                "path",
                null,
                InputOption::VALUE_REQUIRED,
                "Location of existing VirtPHP to clone from.",
                null
            );
    }

    /** 
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $envName = $input->getOption("env");
        $updatedPath = $input->getOption("path");

        // See if a bad sync request was made
        if (
            (isset($envName) && !isset($updatedPath))
            || (!isset($envName) && isset($updatedPath))
        ) {
            $output->writeln(
                "<error>You must provide both an environment"
                . "name and path to resync.</error>"
            );
            
            return false;
        }

        // Check to see if a valid sync request was made
        if (isset($envName) && isset($updatedPath)) {
            // call the resync method
            $sync = $this->getWorker(
                'Shower',
                array($envName, $updatedPath, $output)
            );
            if ($sync->resync()) {
                $output->writeln(
                    '<bg=green;options=bold>' . $envName . ' resynced'
                    . ' successfully to ' . $path . '</bg=green;options=bold>'
                );

                return true;
            }
        }

        // MOVE SOME OF THIS LOGIC TO WORKER
        // Build the path to file
        $envPath = $_SERVER['HOME'] . DIRECTORY_SEPARATOR .  '.virtphp';
        $envFile = 'environments.json';

        // If a resync was not requested, let's check to make sure we have
        // a valid json file to read and read it
        if (
            !$this->getFilesystem()->exists(
                $envPath . DIRECTORY_SEPARATOR . $envFile
            )
        ) {
            $environments = json_decode(file_get_contents($pathToFile), true);
        } else {
            $output->writeln(
                "<error>Either no environments have been created or"
                . " the json file has been moved</error>"
            );
            return false;
        }

        // Logic for listing list of directories 
        $shower = $this->getWorker('Shower', array($output));
        if ($shower->execute()) {
            return true;
        }

        return false;
    }
}
