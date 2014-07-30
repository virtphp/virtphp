<?php

/*
 * This file is part of virtPHP.
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
            ->setDescription('Create new virtPHP environment.')
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
                . 'file WILL BE OVERRIDDEN in order for virtPHP to work!',
                null
            )
			->addOption(
				'fpm-conf',
				null,
				InputOption::VALUE_REQUIRED,
				'Path to a specific php-fpm.conf to use - ',
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

        // Pass the output object to the parent
        $this->output = $output;

        // Check to make sure environment name is valid
        if (!Virtphp::isValidName($envName)) {
            $output->writeln('<error>Sorry, but that is not a valid environment name.</error>');

            return false;
        }

        // Make sure the env hasn't been created before
        if ($this->checkForEnv($envName)) {
            $output->writeln(
                '<error>'
                . 'The environment you specified has already been created.'
                . '</error>'
            );

            return false;
        }

        $binDir = $input->getOption('php-bin-dir');
        $installPath = $input->getOption('install-path');
        if ($installPath === null) {
            $installPath = $envFolder;
        }

        // Setup environment
        $creator = $this->getWorker(
            'Creator',
            array($input, $output, $envName, $installPath, $binDir)
        );
        $creator->setCustomPhpIni($input->getOption('php-ini'));
        $creator->setCustomPearConf($input->getOption('pear-conf'));
        $creator->setCustomFpmConf($input->getOption('fpm-conf'));
        if ($creator->execute()) {
            $output->writeln(
                '<bg=green;options=bold>'
                . "Your virtual php environment ($envName) has been created!"
                . '</bg=green;options=bold>'
            );
            $output->writeln(
                '<info>'
                . "You can activate your new environment using: ~\$ source $installPath/$envName/bin/activate"
                . "</info>\n"
            );

            $this->addEnv($envName, $installPath);

            return true;
        }

        return false;
    }
}
