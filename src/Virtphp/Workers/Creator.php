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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Virtphp\Virtphp;
use Virtphp\Workers\Destroyer;
use InvalidArgumentException;

class Creator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $envName;

    /**
     * @var string
     */
    protected $envBasePath;

    /**
     * @var string
     */
    protected $phpBinDir;

    /**
     * @var string
     */
    protected $customPhpIni = null;

    /**
     * @var string
     */
    protected $customPearConf = null;

    /**
     * @var array
     */
    protected $pearConfigSettings = array();

    /**
     * Default PEAR config options which will be replaced with specific env values
     * @var array
     */
    protected static $DEFAULT_PEAR_CONFIG = array(
        "php_dir"       => "{env_path}/share/php",
        "data_dir"      => "{env_path}/share/php/data",
        "www_dir"       => "{env_path}/share/pear/www",
        "cfg_dir"       => "{env_path}/share/pear/cfg",
        "ext_dir"       => "{env_path}/lib/php",
        "doc_dir"       => "{env_path}/share/php/doc",
        "test_dir"      => "{env_path}/share/pear/tests",
        "cache_dir"     => "{env_path}/share/pear/cache",
        "download_dir"  => "{env_path}/share/pear/download",
        "temp_dir"      => "{env_path}/share/pear/temp",
        "bin_dir"       => "{env_path}/bin",
        "__channels"    => array(
            "pecl.php.net" => array("foo" => "{env_path}/bar"),
            "__uri"        => array(),
            "doc.php.net"  => array(),
        ),
        "php_bin"       => "{env_path}/bin/php",
        "php_ini"       => "{env_path}/etc/php.ini",
        "auto_discover" => 1,
    );

    /**
     * Constructs the Creator worker and initializes various values
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $envName The name of the virtual environment
     * @param string $envBasePath The directory location where the virtual environment should be created
     * @param string $phpBinDir (optional) The directory where the php executable is located
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        $envName,
        $envBasePath,
        $phpBinDir = null
    )
    {
        $this->filesystem = new Filesystem();
        $this->input = $input;
        $this->output = $output;
        $this->setEnvName($envName);
        $this->setEnvBasePath($envBasePath);

        if ($phpBinDir === null) {
            $process = new Process("which php");
            if ($process->run() == 0) {
                $phpBinDir = dirname(trim($process->getOutput()));
            } else {
                throw new \RuntimeException("Can\"t find php on the system. If php is not in the PATH, please specify its location with --php-bin-dir.");
            }
        }

        $this->setPhpBinDir($phpBinDir);
        $this->setPearConfigSettings($this->updatePearConfigSettings(self::$DEFAULT_PEAR_CONFIG));
    }

    /**
     * @return string
     */
    public function getEnvName()
    {
        return $this->envName;
    }

    /**
     * @return string
     */
    public function getEnvBasePath()
    {
        return $this->envBasePath;
    }

    /**
     * @return string
     */
    public function getEnvPath()
    {
        return $this->getEnvBasePath() . DIRECTORY_SEPARATOR . $this->getEnvName();
    }

    /**
     * @return string
     */
    public function getPhpBinDir()
    {
        return $this->phpBinDir;
    }

    /**
     * @return string
     */
    public function getPearConfigSettings()
    {
        return $this->pearConfigSettings;
    }

    /**
     * @return string
     */
    public function getCustomPhpIni()
    {
        return $this->customPhpIni;
    }

    /**
     * @return string
     */
    public function getCustomPearConf()
    {
        return $this->customPearConf;
    }


    /**
     * @param string Name of the new virtual env
     */
    public function setEnvName($name)
    {
        if (!Virtphp::isValidName($name)) {
            throw new \RuntimeException("Environment name must contain only letters, numbers, dashes, and underscores. {$name} is invalid.");
        }

        $this->envName = $name;
    }

    /**
     * @param string Path to install this virtual env into
     */
    public function setEnvBasePath($envBasePath)
    {
        $this->envBasePath = realpath($envBasePath);
    }

    /**
     * @param string Path to php bin directory
     */
    public function setPhpBinDir($phpBinDir)
    {
        $dir = realpath($phpBinDir);
        if (!$this->filesystem->exists($dir)) {
            throw new InvalidArgumentException("The specified php bin directory does not exist.");
        }
        if (!$this->filesystem->exists($dir.DIRECTORY_SEPARATOR."php")) {
            throw new InvalidArgumentException("There is no php binary in the specified directory.");
        }
        $process = new Process($dir.DIRECTORY_SEPARATOR."php -v");
        if ($process->run() != 0) {
            throw new InvalidArgumentException("The \"php\" file in the specified directory is not a valid php binary executable.");
        }

        $this->phpBinDir = $dir;
    }

    /**
     * @param array Specific options to use (hash)
     */
    public function setPearConfigSettings(array $settings = array())
    {
        $this->pearConfigSettings = array_merge(self::$DEFAULT_PEAR_CONFIG, $settings);
    }

    /**
     * @param string Path to custom php.ini file to use
     */
    public function setCustomPhpIni($phpIniFilePath = null)
    {
        if ($phpIniFilePath !== null) {
            $phpIniFilePath = realpath($phpIniFilePath);
            if ($phpIniFilePath === false) {
                $phpIniFilePath = null;
            }
        }
        $this->customPhpIni = $phpIniFilePath;
    }

    /**
     * @param string Path to custom pear.conf file to use
     */
    public function setCustomPearConf($pearConfFilePath = null)
    {
        if ($pearConfFilePath !== null) {
            $pearConfFilePath = realpath($pearConfFilePath);
            if ($pearConfFilePath === false) {
                $pearConfFilePath = null;
            }
        }

        $this->customPearConf = $pearConfFilePath;

        if ($this->customPearConf !== null) {
            $this->output->writeln("Getting custom pear.conf info from ".$this->customPearConf);
            $pearConfFile = file_get_contents($this->customPearConf);
            if ($pearConfFile === false) {
                throw new InvalidArgumentException("Unable to get contents of custom PEAR config file");
            }

            // kill all comment lines
            $pearConfFile = preg_replace("/^\#.*?\n/m", "", $pearConfFile);
            // kill all blank lines
            $pearConfFile = preg_replace("/^\s*?\n/m", "", $pearConfFile);

            $pearConfOptions = unserialize($pearConfFile);
            if ($pearConfOptions === false) {
                throw new InvalidArgumentException("Unable to unserialize custom PEAR config file");
            }

            $pearConfOptions = array_merge($pearConfOptions, self::$DEFAULT_PEAR_CONFIG);
            $this->setPearConfigSettings($this->updatePearConfigSettings($pearConfOptions));
        }
    }


    /**
     * Function is the guts of the worker, reading the provided
     * options and creating the new virtual env.
     *
     * @return boolean Whether or not the action was successful
     */
    public function execute()
    {
        try {

            $this->checkEnvironment();

        } catch (Exception $e) {
            $this->output->writeln("<error>ERROR: ".$e->getMessage()."</error>");

            return false;
        }

        // at this point, if anything fails we need to revert the install
        try {

            $this->createStructure();
            $this->createVersionFile();
            $this->createPhpIni();
            $this->createPhpBinWrapper();
            $this->copyLibraries();
            $this->installPear();
            $this->installComposer();
            $this->copyActivateScript();

        } catch (Exception $e) {
            $this->output->writeln("<error>ERROR: ".$e->getMessage()."</error>");
            $destroyer = new Destroyer($this->input, $this->output, $this->getEnvBasePath());
            $destroyer->execute();
            $this->output->writeln("<info>System reverted</info>");

            return false;
        }

        return true;
    }

    /**
     * Checks that the path for the environment being created doesn"t already
     * exist and the parent directory (basePath) is writable
     *
     * @throws InvalidArgumentException
     */
    protected function checkEnvironment()
    {
        $this->output->writeln("Checking current environment");

        // if the directory exists, use it, otherwise see if it"s relative
        if ($this->filesystem->exists($this->getEnvPath())) {
            throw new InvalidArgumentException("The directory for this environment already exists ({$this->getEnvPath()}).");
        } else if (!is_writable($this->getEnvBasePath())) {
            throw new InvalidArgumentException("The distination directory is not writable, and thus we cannot create the environment.");
        }
    }

    /**
     * Creates all directories for the new virtual env
     */
    protected function createStructure()
    {
        $this->output->writeln("Creating directory structure");

        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "bin");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "etc");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "lib");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "cache");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "cfg");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "download");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "temp");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "tests");
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "www");
    }

    /**
     * Creates the tracking file (.virtphp) which indicates that that 
     * directory is a VirtPHP virtual environment (useful for 
     * Destroying and Cloning)
     */
    protected function createVersionFile()
    {
        $this->output->writeln("Creating VirtPHP version file");

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . ".virtphp",
            Virtphp::VERSION,
            0644
        );
    }

    /**
     * Creates the new php.ini for the virtual env, replacing any 
     * directory paths with custom values
     */
    protected function createPhpIni()
    {
        if ($this->getCustomPhpIni() !== null) {
            $this->output->writeln("Configuring custom php.ini from ".$this->getCustomPhpIni());
            $phpIniPath = $this->getCustomPhpIni();
        } else {
            $this->output->writeln("Creating custom php.ini");
            $phpIniPath = realpath(__DIR__ . "/../../../res/php.ini");
        }

        $phpIni = file_get_contents($phpIniPath);

        if ($this->getCustomPhpIni() === null) {
            // this is our custom php.ini, look for replacement values...
            $phpIni = str_replace(
                "__VIRTPHP_ENV_PHP_INCLUDE_PATH__",
                $this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php",
                $phpIni
            );

            $phpIni = str_replace(
                "__VIRTPHP_ENV_PHP_EXTENSION_PATH__",
                $this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php",
                $phpIni
            );

        } else {
            // user supplied php.ini, so we need to do a bit more work...
            
            // replace any active include_path settings with ours
            if (preg_match("/^\s*include_path\s*\=\s*[^\n]+/im", $phpIni)) {
                $this->output->writeln("  replacing active include_path with virtual env path");
                $phpIni = preg_replace(
                    "/^\s*(include_path\s*\=\s*[^\n]+)/im", 
                    "\n\n;; Old include_path value\n; $1\n".
                        ";; New VirtPHP include_path value:\n".
                        "include_path = \".:".$this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php\"\n", 
                    $phpIni
                );

            } else {
                // there was no active include_path, so add to the end
                $this->output->writeln("  adding new include_path setting with virtual env path");
                
                $phpIni .= "\n\n;; New VirtPHP include_path value:\n".
                           "include_path = \".:".$this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php\"\n";

            }

            // and handle the extension_dir
            // extension_dir = "__VIRTPHP_ENV_PHP_EXTENSION_PATH__"
            if (preg_match("/^\s*extension_dir\s*\=\s*[^\n]+/im", $phpIni)) {
                $this->output->writeln("  replacing active extension_dir with virtual env path");
                $phpIni = preg_replace(
                    "/^\s*(extension_dir\s*\=\s*[^\n]+)/im", 
                    "\n\n;; Old extension_dir value\n; $1\n".
                        ";; New VirtPHP extension_dir value:\n".
                        "extension_dir = \"".$this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php\"\n", 
                    $phpIni
                );

            } else {
                // there was no active extension_dir, so add to the end
                $this->output->writeln("  adding new extension_dir setting with virtual env path");
                
                $phpIni .= "\n\n;; New VirtPHP extension_dir value:\n".
                           "extension_dir = \"".$this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php\"\n";
            }

        }

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "php.ini",
            $phpIni,
            0644
        );
    }

    /**
     * Creates a wrapper shell script around the actual PHP binary in order 
     * to use our custom php.ini (and any other options we may need)
     */
    protected function createPhpBinWrapper()
    {
        $this->output->writeln("Wrapping PHP binary");

        $phpBinWrapper = <<<EOD
#!/bin/bash
{$this->getPhpBinDir()}/php -c {$this->getEnvPath()}/etc/php.ini "$@"
EOD;

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "php",
            $phpBinWrapper,
            0755
        );
    }

    /**
     * [Placeholder method, not currently used]
     */
    protected function copyLibraries()
    {
        //$this->output->writeln("Copying other libraries");
    }

    /**
     * Installs PEAR into the new local virtual environment
     */
    protected function installPear()
    {
        $this->output->writeln("<comment>Downloading pear phar file, this could take a while...</comment>");
        $pearInstall = file_get_contents("http://pear.php.net/install-pear-nozlib.phar");

        $pearBinWrapper = <<<EOD
#!/bin/bash
{$this->getEnvPath()}/bin/pear.pear -c {$this->getEnvPath()}/etc/pear.conf "$@"
EOD;

        $peclBinWrapper = <<<EOD
#!/bin/bash
{$this->getEnvPath()}/bin/pecl.pecl -c {$this->getEnvPath()}/etc/pear.conf "$@"
EOD;

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "install-pear-nozlib.phar",
            $pearInstall,
            0644
        );

        chdir($this->getEnvPath());

        $this->output->writeln("Installing PEAR");
        $process = new Process(
            "/usr/bin/php -n -dshort_open_tag=0 -dopen_basedir= "
            . "-derror_reporting=1803 -dmemory_limit=-1 -ddetect_unicode=0 "
            . "share/install-pear-nozlib.phar "
            . "-d \"share/php\" -b \"bin\" -c \"etc\""
        );

        if ($process->run() != 0) {
            throw new \RuntimeException("Encountered a problem while trying to install PEAR.");
        }

        $this->filesystem->remove($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "install-pear-nozlib.phar");

        $this->output->writeln("Saving pear.conf file.");
        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "pear.conf",
            serialize($this->getPearConfigSettings()),
            0644
        );

        $this->output->writeln("Renaming pear file.");
        $this->filesystem->rename(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pear",
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pear.pear"
        );

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pear",
            $pearBinWrapper,
            0755
        );

        $this->filesystem->rename(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pecl",
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pecl.pecl"
        );

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "pecl",
            $peclBinWrapper,
            0755
        );
    }

    /**
     * Installs Composer into the new local virtual environment
     */
    protected function installComposer()
    {
        $this->output->writeln("Installing Composer locally");

        $process = new Process(
            "curl -sS https://getcomposer.org/installer | php -- --install-dir=\"" . $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin\""
        );

        if ($process->run() != 0) {
            throw new \RuntimeException("Could not install Composer.");
        }

        $this->filesystem->symlink(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar",
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer"
        );
    }

    /**
     * Clones the actiavte script from this library and modifies for the 
     * new virtual environment
     */
    protected function copyActivateScript()
    {
        $this->output->writeln("Installing activate/deactive script");

        $activateScript = file_get_contents(__DIR__ . "/../../../res/activate.sh");

        $activateScript = str_replace("__VIRTPHP_ENV_PATH__", $this->getEnvPath(), $activateScript);

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "activate",
            $activateScript,
            0644
        );
    }

    /**
     * Takes an array of PEAR config options and replaces path values with 
     * alternates that point to the new virtual environment
     *
     * @param  array $pearConfig An array of options to update
     * @return array The modified array of options
     */
    protected function updatePearConfigSettings(array $pearConfig = array())
    {
        foreach ($pearConfig as $key => &$value) {
            if (is_array($value)) {
                $value = $this->updatePearConfigSettings($value);
            }
            if (is_string($value)) {
                $value = str_replace("{env_path}", $this->getEnvPath(), $value);
            }
        }

        return $pearConfig;
    }
}
