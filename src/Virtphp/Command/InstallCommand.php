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
use Virtphp\Workers\Creator;

class InstallCommand extends Command
{

    /**
     * Function that defines command name and what
     * variables we are taking.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('install')
            ->setDescription('Create new virtphp environment.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'What is the name of your environment?'
            )
            ->addOption(
              'php-bin-dir',
              null,
              InputOption::VALUE_REQUIRED,
              'Path to the bin directory for the version of PHP you want to wrap.',
              null
            )
            ->addOption(
              'install-path',
              null,
              InputOption::VALUE_REQUIRED,
              'Base path to install the virtual environment into (do not include the environment name).',
              null
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
        $envName = $input->getArgument('name');

        // Check to make sure environment name is valid
        if (!$this->validName($envName))
        {
            $output->writeln('<error>Sorry, but that is not a valid envronment name.</error>');
            return false;
        }
        
        $binDir = $input->getOption('php-bin-dir');
        $installPath = $input->getOption('install-path');
        if ($installPath === null)
        {
          $installPath = getcwd();
        }

        // Setup environment
        $creator = new Creator($input, $output, $envName, $installPath, $binDir);
        $creator->execute();


        $output->writeln("<bg=green;options=bold>Your're virtual php environment ($envName) has been created.</bg=green;options=bold>");
        $output->writeln("You can activate your new enviornment using: ~\$ virtphp activate $envName");
    }

    /** 
     * Function to make sure provided 
     * environment name is valid.
     *
     * @param string $envName
     */
    protected function validName($envName)
    {
        return preg_match('/^[0-9a-zA-Z_\-]+$/', $envName);
    }
}
