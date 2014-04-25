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

class CreateCommand extends Command
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('create')
            ->setDescription('Create new virtphp environment.')
            ->addArgument(
                'env-name',
                InputArgument::REQUIRED,
                'What is the name of your new environment?'
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
            )
            ->addOption(
                'php-ini',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to a specific php.ini to use - ' .
                'WARNING: the include_path and extension_dir WILL BE OVERRIDDEN!',
                null
            )
            ->addOption(
                'pear-conf',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to a specific pear.conf file to use - '
                . 'WARNING: many of the directory paths in this '
                . 'file WILL BE OVERRIDDEN in order for VirtPHP to work!',
                null
            );
    }

    /*
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $envName = $input->getArgument('env-name');
        $envPath = getenv('HOME') . DIRECTORY_SEPARATOR .  '.virtphp';
        $envFolder = $envPath . DIRECTORY_SEPARATOR . 'envs';

        // Check to make sure environment name is valid
        if (!Virtphp::isValidName($envName)) {
            $output->writeln('<error>Sorry, but that is not a valid environment name.</error>');

            return false;
        }

        $binDir = $input->getOption('php-bin-dir');
        $installPath = $input->getOption('install-path');
        if ($installPath === null) {
            $installPath = $envFolder;
            // check to see if the .virtphp folder exists
            if (!$this->getFilesystem()->exists($envPath)) {
                // create the .virtphp folder
                $this->getFilesystem()->mkdir($envPath);
                // create the env folder
                $this->getFilesystem()->mkdir($envFolder);
            }
        }

        // Check for old .pearrc file conflict
        if ($this->getFilesystem()->exists(getenv('HOME').'/.pearrc')) {
            $output->writeln(
                '<warning>There is an old .pearrc file on your '
                . 'system that may prevent this VirtPHP env from being created.'
                . ' If an error occurs, you may temporarily move the .pearrc file '
                . 'while creating your virtual env.</warning>'
            );
        }

        // Setup environment
        $creator = $this->getWorker('Creator', array($input, $output, $envName, $installPath, $binDir));
        $creator->setCustomPhpIni($input->getOption('php-ini'));
        $creator->setCustomPearConf($input->getOption('pear-conf'));
        if ($creator->execute()) {
            $output->writeln(
                '<bg=green;options=bold>'
                . "Your virtual php environment ($envName) has been created!"
                . '</bg=green;options=bold>'
            );
            $output->writeln(
                '<info>'
                . "You can activate your new environment using: ~\$ source $installPath/bin/activate"
                . "</info>\n"
            );

            return true;
        }

        return false;
    }
}
