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

namespace Virtphp\Workers;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Cloner extends AbstractWorker
{

    /**
     * @var string
     */
    protected $originalPath = array();

    /**
     * @var string
     */
    protected $fullPath;

    /**
     * @var string
     */
    protected $envName;

    /**
     * @var string
     */
    protected $realPath;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructs the clone worker
     *
     * @param string $originalPath
     * @param string $envName
     * @param OutputInterface $output
     */
    public function __construct($originalPath, $envName, OutputInterface $output)
    {
        $this->originalPath = realpath($originalPath);
        $this->envName = $envName;
        $this->output = $output;
    }

    /**
     * Function is the guts of the worker, reading the provided
     * directory and copying those files over.
     *
     * @return boolean Whether or not the action was successful
     */
    public function execute()
    {
        $this->getFilesystem()->mkdir($this->envName);
        $this->realPath = realpath($this->envName);
        $this->output->writeln(
            "<comment>" .
            "Cloning virtPHP env from " .
            $this->originalPath .
            " to " .
            $this->realPath .
            "</comment>"
        );

        try {

            $this->cloneEnv();
            $this->updateActivateFile();
            $this->updatePhpIni();
            $this->createPhpBinWrapper();
            $this->sourcePear();
            $this->output->writeln("Setting proper permissions on cloned bin directory");
            $this->getFilesystem()->chmod(
                $this->realPath . DIRECTORY_SEPARATOR . "bin",
                0755,
                0000,
                true
            );
            $this->addEnvToFile();

            return true;

        } catch (\Exception $e) {

            $this->getFilesystem()->remove($this->realPath);
            $this->output->writeln("<error>Error: cloning directory failed.</error>");

            return false;

        }

    }

    /**
     * Function gets the real path value of new virtPHP environment
     * copies over all the files and folders to the new virtPHP environment
     * and creates the fullPath property.
     */
    protected function cloneEnv()
    {
        $this->output->writeln("Copying contents of " . $this->originalPath . " to " . $this->realPath);
        $this->getFilesystem()->mirror($this->originalPath, $this->realPath);
    }

    /**
     * Function takes the contents of the original activate file, replaces the path
     * with a reference to the new virtPHP, deletes the file, then saves an updated
     * version.
     */
    protected function updateActivateFile()
    {
        $this->output->writeln("Updating activate file.");
        // Get paths for files and folders
        $activateFilePath = $this->realPath . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "activate";
        if (!$this->getFilesystem()->exists($activateFilePath)) {
            $activateFilePath .= ".sh";
        }

        // GET activate of new directory to replace path variable
        $originalContents = $this->getFilesystem()->getContents($activateFilePath);

        // Replace paths from old env to new cloned env
        $newContents = str_replace($this->originalPath, $this->realPath, $originalContents);

        // remove file to avoid collision
        if ($this->getFilesystem()->exists($activateFilePath)) {
            $this->getFilesystem()->remove($activateFilePath);
        }

        // Write activate file again
        $this->getFilesystem()->dumpFile($activateFilePath, $newContents, 0644);
    }

    /**
     * Updates paths in new php.ini file
     */
    protected function updatePhpIni()
    {
        $this->output->writeln("Updating PHP ini file.");

        // Get paths for files and folders
        $sharePath = DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php";
        $libPath = DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php";
        $iniPHPLocation = $this->realPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "php.ini";

        $phpIni = $this->getFilesystem()->getContents($iniPHPLocation);

        // Replace the share path
        $phpIni = str_replace(
            $this->originalPath . $sharePath,
            $this->realPath . $sharePath,
            $phpIni
        );

        // Replace the lib path
        $phpIni = str_replace(
            $this->originalPath . $libPath,
            $this->realPath . $libPath,
            $phpIni
        );

        $this->getFilesystem()->dumpFile(
            $iniPHPLocation,
            $phpIni,
            0644
        );
    }

    /**
     * Creates new PHP bin wrapper with new paths
     */
    protected function createPhpBinWrapper()
    {
        $this->output->writeln("Updating PHP bin wrapper.");
        $phpBinWrapPath = $this->realPath . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "php";
        $newIniPath = $this->realPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "php.ini";

        $currentWrapper = $this->getFilesystem()->getContents($phpBinWrapPath);

        $newWrapper = str_replace(
            $this->originalPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "php.ini",
            $this->realPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "php.ini",
            $currentWrapper
        );

        $this->getFilesystem()->dumpFile(
            $this->realPath . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "php",
            $newWrapper,
            0644
        );
    }

    /**
     * Updates PEAR and config settings for new environment
     */
    protected function sourcePear()
    {
        $this->output->writeln("Updating virtual PEAR install and config");

        $pearConfigContents = $this->getFilesystem()->getContents(
            $this->realPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "pear.conf"
        );
        $pearConfigArray = unserialize($pearConfigContents);

        $newPearConfig = serialize($this->processConfigSettings($pearConfigArray));

        $this->getFilesystem()->dumpFile(
            $this->realPath . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "pear.conf",
            $newPearConfig,
            0644
        );
    }

    /**
     * Replaces original path with new path in pear config file
     *
     * @param  array $pearConfig The old array of config options
     * @return array The new array of config options
     */
    protected function processConfigSettings(array $pearConfig = array())
    {
        foreach ($pearConfig as $key => &$value) {
            if (is_array($value)) {
                $value = $this->processConfigSettings($value);
            }
            if (is_string($value)) {
                $value = str_replace($this->originalPath, $this->realPath, $value);
            }
        }

        return $pearConfig;
    }

    /**
     * Method to check to see if env json file is there and if not create it and
     * add new env with path
     */
    protected function addEnvToFile()
    {
        $envPath = $_SERVER['HOME'] . DIRECTORY_SEPARATOR .  '.virtphp';
        $envFile = 'environments.json';

        $this->output->writeln(
            'Getting the contents of current environments file.'
        );
        // get contents, convert to array, add this env and path
        $envContents = $this->getFilesystem()->getContents(
            $envPath . DIRECTORY_SEPARATOR . $envFile
        );

        // Convert the contents to array
        $envList = json_decode($envContents, true);

        // Create new record to add
        $newRecord = array(
            'name' => $this->envName,
            'path' => $this->realPath,
        );

        // Add to final object and then write to file
        $envList[$this->envName] = $newRecord;

        $this->output->writeln(
            'Write updated list to environments file.'
        );
        $this->getFilesystem()->dumpFile(
            $envPath . DIRECTORY_SEPARATOR . $envFile,
            json_encode($envList)
        );
    }
}
