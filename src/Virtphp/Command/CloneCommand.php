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
            ->setName('clone')
            ->setDescription('Create new virtphp from existing path.')
            ->addArgument(
                'new-env-name',
                InputArgument::REQUIRED,
                'What is the name of your new environment'
            )
            ->addArgument(
                'existing-env-path',
                InputArgument::REQUIRED,
                'Location of existing VirtPHP to clone from.'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $envName = $input->getArgument('new-env-name');
        $copyEnv = $input->getArgument('existing-env-path');

        // Pass the output object to the parent
        $this->output = $output;

        if (!Virtphp::isValidName($envName)) {
            $output->writeln('<error>Sorry, but that is not a valid environment name.</error>');

            return false;
        }

        $env = $this->checkForEnv($copyEnv);
        // Validate the provided directory contains what we need
        if (!$this->isValidPath($env, $output)) {
            return false;
        }

        // Logic for cloning directory
        $cloner = $this->getWorker(
            'Cloner',
            array(
                $env['path'] . DIRECTORY_SEPARATOR . $env['name'],
                $envName,
                $output
            )
        );
        if ($cloner->execute()) {
            $output->writeln(
                '<bg=green;options=bold>'
                . 'Your new cloned virtual php environment has been created.'
                . '</bg=green;options=bold>'
            );
            $output->writeln('<info>Cloned from: $rootPath</info>');

            // Add new env to list
            $this->addEnv($envName);

            return true;
        }

        return false;
    }

    /**
     * Function to check path for valid binary location
     *
     * @param string $rootPath
     * @param  OutputInterface $output The output to use for the messages
     * @return  boolean
     */
    protected function isValidPath($env, OutputInterface $output)
    {
        $filesystem = $this->getFilesystem();

        // make sure we have an entry for the copy env
        if (!$env) {
            $output->writeln(
                '<error>Sorry, but the environment provided to copy has not '
                . 'be created.</error>'
            );

            return false;
        }

        $rootPath = $env['path'] . DIRECTORY_SEPARATOR . $env['name'];

        if (!$filesystem->exists($rootPath)) {
            $output->writeln(
                '<error>Sorry, but there is no VirtPHP environment at that '
                . 'location.</error>'
            );

            return false;
        }

        if (!$filesystem->exists($rootPath . DIRECTORY_SEPARATOR . '.virtphp')) {
            $output->writeln(
                '<error>This directory does not contain a valid VirtPHP '
                . 'environment!</error>'
            );

            return false;
        }

        return true;
    }

}
