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
use Symfony\Component\Filesystem\Filesystem;
use Virtphp\Workers\CloneWorker;

class CloneCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input = null;

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var FileSystem 
     */
    protected $filesystem = null;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $env_name;

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
                InputArgument::REQUIRED,
                'What is the name of your environment'
            )
            ->addOption(
                'original',
                null,
                InputOption::VALUE_REQUIRED,
                'Location of existing VirtPHP to clone from.'
            );
    }

    /** 
     * Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->env_name = $input->getArgument('name');
        $this->rootPath = $input->getOption('original');
        $this->filesystem = new Filesystem();
        $this->output = $output;

        if (!$this->env_name && $this->validName()) {
            $output->writeln('<error>To create a new VirtPHP, you must provide a name.</error>');
            return false;
        }

        if (!$this->rootPath) {
            $output->writeln('<error>We need a path to clone from.</error>');
            return false;
        }

        if (substr($this->rootPath, -1) === '/') {
            $this->rootPath = substr($this->rootPath, 0, -1);
        }

        // Validate the provided directory contains what we need
        if (!$this->checkPath()) {
            return false;
        }

        // Logic for cloning directory 
        $clone_worker = new CloneWorker($this->rootPath, $this->env_name, $output);
        $cloneError = $clone_worker->execute();
        if (!$cloneError) {
            $output->writeln("<bg=green;options=bold>Your new cloned virtual php environment has been created.</bg=green;options=bold>");
            $output->writeln("<info>Cloned from: $this->rootPath</info>");
        } else {
            $output->writeln("<bg=red;options=bold>Issue cloning.</bg=red;options=bold>");
        }

    }

    /** 
     * Function to check path for valid binary
     *
     * @param string $path
     */
    protected function checkPath()
    {
        // Logic to check directory before clone
        if (!$this->filesystem->exists(
            $this->rootPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . ".virtphp"
        )) {
            $this->output->writeln("<error>This directory does not contain a valid VirtPHP environment!</error>");
            return false; 
        }

        return true;
    }

    /** 
     * Function to make sure provided 
     * environment name is valid.
     *
     * @param string $env_name
     */
    function validName()
    {
        $name = $this->env_name;
        if (is_numeric($name[0])) {
            $this->output->writeln("<error>Project names can not start with a number.</error>");
            return false;
        } else if (strpos($name, '/')) {
            $this->output->writeln("<error>New environment name contains unsupported characters.</error>");
            return false;
        }
        // Logic for validating name
        return true;
    }
}
