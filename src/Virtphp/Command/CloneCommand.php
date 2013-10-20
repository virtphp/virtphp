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

class CloneCommand extends Command
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName("clone")
            ->setDescription("Create new virtphp from existing path.")
            ->addArgument(
                "new-env-name",
                InputArgument::REQUIRED,
                "What is the name of your new environment"
            )
            ->addArgument(
                "existing-env-path",
                InputArgument::REQUIRED,
                "Location of existing VirtPHP to clone from."
            );
    }

    /** 
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $envName = $input->getArgument("new-env-name");
        $rootPath = realpath($input->getArgument("existing-env-path"));

        if (!Virtphp::isValidName($envName)) {
            $output->writeln("<error>Sorry, but that is not a valid environment name.</error>");
            
            return false;
        }

        // Validate the provided directory contains what we need
        if (!$this->isValidPath($rootPath, $output)) {
            return false;
        }

        // Logic for cloning directory
        $cloner = $this->getWorker('Cloner', array($rootPath, $envName, $output));
        if ($cloner->execute()) {
            $output->writeln("<bg=green;options=bold>Your new cloned virtual php environment has been created.</bg=green;options=bold>");
            $output->writeln("<info>Cloned from: $rootPath</info>");

            return true;
        }

        return false;
    }

    /** 
     * Function to check path for valid binary location
     *
     * @param string $path
     * @param  OutputInterface $output The output to use for the messages
     * @return  boolean
     */
    protected function isValidPath($rootPath, OutputInterface $output)
    {
        $filesystem = $this->getFilesystem();

        if (!$filesystem->exists($rootPath)) {
            $output->writeln("<error>Sorry, but there is no VirtPHP environment at that location.</error>");
            
            return false;
        }

        if (!$filesystem->exists($rootPath . DIRECTORY_SEPARATOR . ".virtphp")) {
            $output->writeln("<error>This directory does not contain a valid VirtPHP environment!</error>");

            return false;
        }

        return true;
    }

}
