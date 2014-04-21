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
                "New correct path for the provided environment.",
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
        if ((isset($envName) && !isset($updatedPath))
            || (!isset($envName) && isset($updatedPath))
        ) {
            $output->writeln(
                "<error>You must provide both an environment" .
                "name and path to resync.</error>"
            );

            return false;
        }

        // Check to see if a valid sync request was made
        if (isset($envName) && isset($updatedPath)) {
            // call the resync method
            $sync = $this->getWorker(
                'Shower',
                array($output)
            );
            if ($sync->updatePath($envName, $updatedPath)) {
                return true;
            }
        }

        // Logic for listing list of directories
        $shower = $this->getWorker('Shower', array($output));
        if ($shower->execute()) {
            return true;
        }

        return false;
    }
}
