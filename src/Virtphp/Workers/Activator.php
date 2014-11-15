<?php

/**
 * This file is part of virtPHP.
 *
 * (c) Jordan Kasper <github @jakerella>
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Workers;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Activator extends AbstractWorker
{

    /**
     * @var string
     */
    protected $envName;

    /**
     * @var string
     */
    protected $envPath;

    /**
     * @var string
     */
    protected $envFile;

    /**
     * @var string
     */
    protected $file = 'environments.json';

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $tableHelper;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructs the shower worker
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output, $envName, $envFile)
    {
        $this->envName = $envName;
        $this->envFile = $envFile;
        $this->envPath = getenv('HOME') . DIRECTORY_SEPARATOR . '.virtphp';
        $this->filePath = $this->envPath . DIRECTORY_SEPARATOR. $this->file;
        $this->output = $output;
        $this->tableHelper = new TableHelper();
    }

    /**
     * Function is the guts of the worker, reading the provided
     * directory and copying those files over.
     *
     * @return boolean Whether or not the action was successful
     */
    public function execute()
    {
        // If a resync was not requested, let's check to make sure we have
        // a valid json file to read and read it
        if ($this->getFilesystem()->exists($this->filePath)) {

            // Do a search for the provided environment
            $envFile = $this->envFile->checkForEnvironment($this->envName);

            // make sure we found the environment
            if (!$envFile) {
                $this->output->writeln(
                    '<error>Could not find the environment you asked for.</error>'
                );
                return false;
            }

            // build source path
            $path = $envFile['path'] . DIRECTORY_SEPARATOR . $envFile['name']
                . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'activate';

            // Initiative copy to clipboard process
            if (!$this->copyToClipboard('source ' . $path)) {
                $this->output->writeln(
                    '<error>Could not copy the path to your clipboard. '
                    . 'Please copy the instructions below.</error>'
                );
            } else {
                $this->output->writeln(
                    '<info>Congrats, we were able to save the source command to '
                    . 'your clipboard. </info>'
                );
                $this->output->writeln(
                    '<info>Just hit your paste command to activate this '
                    . 'environment or follow the instructions below.</info>'
                );
                $this->output->writeln('');
                $this->output->writeln(
                    '<info>or</info>'
                );
            }

            $this->output->writeln('');
            $this->output->writeln(
                '<info>Copy and paste this code to activate your environment.</info>'
            );
            $this->output->writeln('<comment>source ' . $path . '</comment>');
            $this->output->writeln('');

        } else {
            $this->output->writeln(
                '<error>either no environments have been created on this system'
                . ' or the json file has been moved</error>'
            );
            return false;
        }

        return true;
    }

    /**
     * Method for getting the current OS
     *
     */
    public function getOs()
    {
        // get the current os
        $os = $this->getProcess('uname');

        if ($os->run() == 0) {
            return $os->getOutput();
        } else {
            return 'error';
        }
    }

    /**
     * Method for copying source to the clipboard
     */
    public function copyToClipboard($source)
    {
        $os = trim($this->getOs());

        if ($os === 'Darwin') {
            $process = $this->getProcess('echo "' . $source . '" | pbcopy');
            if ($process->run() == 0) {
                return true;
            }
        } elseif ($os === 'Linux') {
            $process = $this->getProcess('echo "' . $source . '" | xclip');
            if ($process->run() == 0) {
                return true;
            }
        }

        return false;
    }
}
