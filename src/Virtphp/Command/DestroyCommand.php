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
use Virtphp\Workers\Destroyer;

class DestroyCommand extends Command
{

    /**
     * Defines command name and what arguments and options we are taking.
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName("destroy")
            ->setDescription("Destroy an existing virtual environment.")
            ->addArgument(
                "path",
                InputArgument::REQUIRED,
                "Please specify the path to the virtual environment you want to destroy."
            );
    }

    /*
     * Process input options for command and execute functionality.
     * Automatically run by Symfony Console
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean Whether the action completed successfully and Console activity should continue
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument("path");

        $virtPath = getenv("VIRTPHP_ENV_PATH");
        if ($virtPath !== false && $virtPath == realpath($path)) {
            $output->writeln("<error>You must deactivate this virtual environment before destroying it!</error>");
            
            return false;
        }

        $dialog = $this->getHelperSet()->get("dialog");
        if (!$dialog->askConfirmation(
                $output,
                "<question>Are you sure you want to delete this virtual environment?\nDirectory: $path\nWARNING: ALL FILES WILL BE REMOVED IN THIS DIRECTORY! (y/N): </question>",
                false
            )) {
            $output->writeln("<info>This action has been cancelled.</info>");
            
            return false;
        }

        // Setup environment
        $creator = new Destroyer($input, $output, $path);
        if ($creator->execute()) {
            $output->writeln("<bg=green;options=bold>Yourr virtual PHP environment has been destroyed.</bg=green;options=bold>");
            $output->writeln("<info>We deleted the contents of: $path</info>");

            return true;
        }

        return false;
    }

}
