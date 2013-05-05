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
use Symfony\Component\Process\Process;
use Virtphp\Workers\Activator;


class ActivateCommand extends Command
{

    /**
     * Function that defines command name and what
     * variables we are taking.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('activate')
            ->setDescription('Create new virtphp from scratch.')
            ->addArgument(
                'virt_env',
                InputArgument::OPTIONAL,
                'Which virtual environment would you like to use?'
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
        $virt_env = $input->getArgument('virt_env');

        if ($virt_env && !$this->validEnvironment($virt_env)) {
            $output->writeln('<bg=red>The name provided is not valid or references an environment that has not been setup yet.</bg=red>');
            return false;
        }

        /*$env_activate = new Process('sudo -u Kite bin/activate');
        $env_activate->setTimeout(3600);
        $env_activate->run();

        if (!$env_activate->isSuccessful()) {
            $output->writeln('<bg=red>There was an issue activating the environment</bg=red');
            $output->writeln('<bg=red>"' . $env_activate->getErrorOutput() . '"</bg=red');
        } else {
            $output->writeln("<bg=yellow>" . $env_activate->getOutput() . "</bg=yellow>");
        }*/
        $here = exec('source bin/activate');
        echo $here;
        $output->writeln('<bg=green>Activated ' . $virt_env . '</bg=green>'); // delete after setup
    }

    /** 
     * Function to check if the provided virtphp env name
     * is valid.
     *
     * @param string $virt_env
     */
    function validEnvironment($virt_env)
    {
        // Logic for validating name
        return true;
    }
}
