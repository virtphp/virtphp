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
use Virtphp\Workers\Cloner;

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
    private $envName;


    /** 
     * Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->envName = $input->getArgument('name');
        $this->rootPath = $input->getArgument('original');
        $this->filesystem = new Filesystem();
        $this->output = $output;

        if (!Virtphp::isValidName($this->envName)) {
            $output->writeln('<error>Sorry, but that is not a valid environment name.</error>');
            return false;
        }

        if (substr($this->rootPath, -1) === '/') {
            $this->rootPath = substr($this->rootPath, 0, -1);
        }

        // Validate the provided directory contains what we need
        if (!$this->isValidPath($this->rootPath)) {
            return false;
        }

        // Logic for cloning directory 
        $cloner = new Cloner($this->rootPath, $this->envName, $output);
        if ($cloner->execute()) {
            $output->writeln("<bg=green;options=bold>Your new cloned virtual php environment has been created.</bg=green;options=bold>");
            $output->writeln("<info>Cloned from: $this->rootPath</info>");
        }

    }

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
            ->addArgument(
                'original',
                InputArgument::REQUIRED,
                'Location of existing VirtPHP to clone from.'
            );
    }

    /** 
     * Function to check path for valid binary
     *
     * @param string $path
     * @return  boolean
     */
    protected function isValidPath($rootPath)
    {
        // Logic to check directory before clone
        if (!$this->filesystem->exists(
            $rootPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . ".virtphp"
        )) {
            $this->output->writeln("<error>This directory does not contain a valid VirtPHP environment!</error>");

            return false;
        }

        return true;
    }

}
