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

namespace Virtphp\Util;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Virtphp\Util\Filesystem;

class EnvironmentFile
{
    public $envFile;

    public $envContents = array();

    public $output;

    public function __construct(OutputInterface $output)
    {
        // set the path to the environments folder
        $this->envFile = getenv('HOME') . DIRECTORY_SEPARATOR . '.virtphp' . DIRECTORY_SEPARATOR .  'environments.json';
        $this->envPath = getenv('HOME') . DIRECTORY_SEPARATOR . '.virtphp';
        $this->envFolder = $this->envPath . DIRECTORY_SEPARATOR . 'envs';
        $this->output = $output;

        // Make sure the ~/.virtphp and associated files/folders are created
        $this->createEnvironmentsFile();
        // Get the contents of the environments.json file
        $contents = $this->getFilesystem()->getContents($this->envFile);
        // Set as class property
        $this->envContents = json_decode($contents, true);
    }

    /**
     * Returns a Filesystem object for executing filesystem operations
     *
     * @return \Virtphp\Util\Filesystem
     */
    public function getFilesystem()
    {
        return new Filesystem();
    }

    /**
     * Method for returning an array of environments
     *
     * @return array Array of created environments
     */
    public function getEnvironments()
    {
        return $this->envContents;
    }

    /**
     * Method to search list for one particular environment
     *
     * @return array Array of created environments
     */
    public function checkForEnvironment($env)
    {
        // do a check to see if the environment is there
        if (isset($this->envContents[$env])) {
            return $this->envContents[$env];
        } else {
            return false;
        }
    }

    /**
     * Method for creating the ~/.virtphp/environments.json file
     *
     */
    public function createEnvironmentsFile()
    {
        // Check to see if the .virtphp directory is there if not, make one
        if (!$this->getFilesystem()->exists($this->envPath)) {
            $this->output->writeln(
                'Creating .virtphp directory in user home folder.'
            );
            $this->getFilesystem()->mkdir($this->envPath);
        }
        // Check to see if the environments.json file is there
        if (!$this->getFilesystem()->exists($this->envFile)) {
            $this->output->writeln(
                'Creating the environments.json file.'
            );
            $this->getFilesystem()->touch($this->envFile);
        }
        // Finally check to see if the env folder is there
        if (!$this->getFilesystem()->exists($this->envFolder)) {
            $this->output->writeln(
                'Creating the env folder to hold the environments.'
            );
            $this->getFilesystem()->mkdir($this->envFolder);
        }

        return true;
    }

    /*
     * Method for adding record to the environments.json file.
     *
     * @param string $envName Name of the env to add.
     * @param string $envPath Path, if custom, to where the env is located.
     *
     * @return boolean
     */
    public function addEnv($envName, $envPath = '')
    {
        if (empty($envPath)) {
            $envPath = $this->envFolder;
        }

        // Create new record to add
        $newRecord = array('name' => $envName, 'path' => $envPath);

        $this->output->writeln(
            'Getting the contents of current environments file.'
        );

        // Add to final object and then write to file
        $localEnvs = $this->envContents;
        $localEnvs[$envName] = $newRecord;
        $this->envContents = $localEnvs;

        $this->output->writeln(
            'Writing updated list to environments file.'
        );
        try {
            $this->getFilesystem()->dumpFile($this->envFile, json_encode($this->envContents));
        } catch (\Exception $e) {
            $this->output->writeln(
                'Something went wrong adding env to list. ' . $e
            );

            return false;
        }

        return true;
    }

    /**
     * Method for removing environment from list.
     *
     * @param string $env The env to remove
     */
    public function removeEnvFromList($path)
    {
        // clean the path first
        // make sure the trailing / is removed if autocompleted
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        // Convert to an array if full path
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (is_array($path)) {
            // grab the last entry which is the name we are looking for
            $path = $path[count($path) - 1 ];
        }

        if (isset($this->envContents[$path])) {
            $this->output->writeln(
                '<info>Found path and removed from list. '
                . $path . '</info>'
            );
            // Where the delete happens
            unset($this->envContents[$path]);
        } else {
            $this->output->writeln(
                '<info>No matching environments in list archive. '
                . $path . '</info>'
            );

            return false;
        }

        // Write File
        try {
            $this->getFilesystem()->dumpFile($this->envFile, json_encode($this->envContents));
        } catch (\Exception $e) {
            $this->output->writeln(
                'Something went wrong adding env to list. ' . $e
            );

            return false;
        }

        return true;
    }
}
