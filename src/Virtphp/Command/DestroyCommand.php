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

class DestroyCommand extends Command
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('destroy')
            ->setDescription('Destroy an existing virtual environment.')
            ->addArgument(
                'env-path',
                InputArgument::REQUIRED,
                'Please specify the path to the virtual environment you want to destroy.'
            );
    }

    /*
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('env-path');

        $virtPath = getenv('VIRTPHP_ENV_PATH');
        if ($virtPath !== false && $virtPath == realpath($path)) {
            $output->writeln(
                '<error>' .
                'You must deactivate this virtual environment before destroying it!' .
                '</error>'
            );

            return false;
        }

        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog->askConfirmation(
            $output,
            '<question>' .
            'Are you sure you want to delete this virtual environment?\n' .
            "Directory: $path\nWARNING: ALL FILES WILL BE REMOVED IN THIS DIRECTORY! (y/N): " .
            '</question>',
            false
        )) {
            $output->writeln('<info>This action has been canceled.</info>');

            return false;
        }

        // Destroy environment
        $destroyer = $this->getWorker('Destroyer', array($input, $output, $path));
        if ($destroyer->execute()) {
            $output->writeln(
                '<bg=green;options=bold>' .
                'Your virtual PHP environment has been destroyed.' .
                '</bg=green;options=bold>'
            );
            $output->writeln("<info>We deleted the contents of: $path</info>");

            return true;
        }

        return false;
    }

}
