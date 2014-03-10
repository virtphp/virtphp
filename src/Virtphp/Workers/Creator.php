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
use Symfony\Component\Process\Process;
use Virtphp\Virtphp;
use InvalidArgumentException;

class Creator extends AbstractWorker
{
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
        $this->input = $input;
        $this->output = $output;
        $this->setEnvName($envName);
        $this->setEnvBasePath($envBasePath);

        if ($phpBinDir === null) {
            $process = $this->getProcess("which php");
            if ($process->run() == 0) {
                $phpBinDir = $this->getFilesystem()->dirname(trim($process->getOutput()));
            } else {
                throw new \RuntimeException("Can't find php on the system. If php is not in the PATH, please specify its location with --php-bin-dir.");
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
    public function getEnvBinDir()
    {
        return $this->getEnvPath() . DIRECTORY_SEPARATOR . "bin";
    }

    /**
     * @return string
     */
    public function getEnvPhpExtDir()
    {
        return $this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php";
    }

    /**
     * @return string
     */
    public function getEnvPhpIncDir()
    {
        return $this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php";
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
        $this->envBasePath = $this->getFilesystem()->realpath($envBasePath);
    }

    /**
     * @param string Path to php bin directory
     */
    public function setPhpBinDir($phpBinDir)
    {
        $dir = $this->getFilesystem()->realpath($phpBinDir);
        if (!$this->getFilesystem()->exists($dir)) {
            throw new InvalidArgumentException("The specified php bin directory does not exist.");
        }
        if (!$this->getFilesystem()->exists($dir.DIRECTORY_SEPARATOR."php")) {
            throw new InvalidArgumentException("There is no php binary in the specified directory.");
        }
        $process = $this->getProcess($dir.DIRECTORY_SEPARATOR."php -v");
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
            $phpIniFilePath = $this->getFilesystem()->realpath($phpIniFilePath);
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
            $pearConfFilePath = $this->getFilesystem()->realpath($pearConfFilePath);
            if ($pearConfFilePath === false) {
                $pearConfFilePath = null;
            }
        }

        $this->customPearConf = $pearConfFilePath;

        if ($this->customPearConf !== null) {
            $this->output->writeln("Getting custom pear.conf info from ".$this->customPearConf);
            $pearConfFile = $this->getFilesystem()->getContents($this->customPearConf);
            if ($pearConfFile === false) {
                throw new InvalidArgumentException("Unable to get contents of custom PEAR config file");
            }

            // kill all comment lines
            $pearConfFile = preg_replace("/^\#.*?\n/m", "", $pearConfFile);
            // kill all blank lines
            $pearConfFile = preg_replace("/^\s*?\n/m", "", $pearConfFile);

            $pearConfOptions = @unserialize($pearConfFile);
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

        } catch (\Exception $e) {
            $this->output->writeln("<error>ERROR: ".$e->getMessage()."</error>");

            return false;
        }

        // at this point, if anything fails we need to revert the install
        try {

            $this->createStructure();
            $this->createVersionFile();
            $this->createPhpIni();
            $this->createPhpBinWrapper();
            $this->installPear();
            $this->installPhpConfigPhpize();
            $this->installComposer();
            $this->copyActivateScript();

        } catch (\Exception $e) {
            $this->output->writeln("<error>ERROR: ".$e->getMessage()."</error>");
            $this->getDestroyer()->execute();
            $this->output->writeln("<info>System reverted</info>");

            return false;
        }

        // Now, let's see if the the environments file has been created
        $this->addEnvFile();

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
        if ($this->getFilesystem()->exists($this->getEnvPath())) {
            throw new InvalidArgumentException("The directory for this environment already exists ({$this->getEnvPath()}).");
        } else if (!$this->getFilesystem()->isWritable($this->getEnvBasePath())) {
            throw new InvalidArgumentException("The destination directory is not writable, and thus we cannot create the environment.");
        }
    }

    /**
     * Creates all directories for the new virtual env
     */
    protected function createStructure()
    {
        $this->output->writeln("Creating directory structure");

        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "bin");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "etc");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "lib");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "php");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "cache");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "cfg");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "download");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "temp");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "tests");
        $this->getFilesystem()->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear" . DIRECTORY_SEPARATOR . "www");
    }

    /**
     * Creates the tracking file (.virtphp) which indicates that that 
     * directory is a VirtPHP virtual environment (useful for 
     * Destroying and Cloning)
     */
    protected function createVersionFile()
    {
        $this->output->writeln("Creating VirtPHP version file");

        $this->getFilesystem()->dumpFile(
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
            $phpIniPath = __DIR__ . "/../../../res/php.ini";
        }

        $phpIni = $this->getFilesystem()->getContents($phpIniPath);

        if ($this->getCustomPhpIni() === null) {
            // this is our custom php.ini, look for replacement values...
            $phpIni = str_replace(
                "__VIRTPHP_ENV_PHP_INCLUDE_PATH__",
                $this->getEnvPhpIncDir(),
                $phpIni
            );

            $phpIni = str_replace(
                "__VIRTPHP_ENV_PHP_EXTENSION_PATH__",
                $this->getEnvPhpExtDir(),
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
                        "include_path = \".:" . $this->getEnvPhpIncDir() . "\"\n",
                    $phpIni
                );

            } else {
                // there was no active include_path, so add to the end
                $this->output->writeln("  adding new include_path setting with virtual env path");
                
                $phpIni .= "\n\n;; New VirtPHP include_path value:\n".
                           "include_path = \".:" . $this->getEnvPhpIncDir() . "\"\n";

            }

            // and handle the extension_dir
            // extension_dir = "__VIRTPHP_ENV_PHP_EXTENSION_PATH__"
            if (preg_match("/^\s*extension_dir\s*\=\s*[^\n]+/im", $phpIni)) {
                $this->output->writeln("  replacing active extension_dir with virtual env path");
                $phpIni = preg_replace(
                    "/^\s*(extension_dir\s*\=\s*[^\n]+)/im",
                    "\n\n;; Old extension_dir value\n; $1\n".
                        ";; New VirtPHP extension_dir value:\n".
                        "extension_dir = \"" . $this->getEnvPhpExtDir() . "\"\n",
                    $phpIni
                );

            } else {
                // there was no active extension_dir, so add to the end
                $this->output->writeln("  adding new extension_dir setting with virtual env path");
                
                $phpIni .= "\n\n;; New VirtPHP extension_dir value:\n".
                           "extension_dir = \"" . $this->getEnvPhpExtDir() . "\"\n";
            }

        }

        $this->getFilesystem()->dumpFile(
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
#!/bin/sh
exec {$this->getPhpBinDir()}/php -c {$this->getEnvPath()}/etc/php.ini "$@"
EOD;

        $this->getFilesystem()->dumpFile(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "php",
            $phpBinWrapper,
            0755
        );
    }

    /**
     * Installs PEAR into the new local virtual environment
     */
    protected function installPear()
    {
        $this->output->writeln("<comment>Downloading pear phar file, this could take a while...</comment>");
        $pearInstall = $this->getFilesystem()->getContents("http://pear.php.net/install-pear-nozlib.phar");

        $pearBinSource = <<<EOD
#!/bin/sh
exec {$this->getEnvBinDir()}/php -C -q -d include_path={$this->getEnvPhpIncDir()} -d date.timezone=UTC -d output_buffering=1 -d variables_order=EGPCS -d open_basedir="" -d safe_mode=0 -d register_argc_argv="On" -d auto_prepend_file="" -d auto_append_file="" {$this->getEnvPhpIncDir()}/pearcmd.php -c {$this->getEnvPath()}/etc/pear.conf "$@"
EOD;

        $peclBinSource = <<<EOD
#!/bin/sh
exec {$this->getEnvBinDir()}/php -C -n -q -d include_path={$this->getEnvPhpIncDir()} -d date.timezone=UTC -d output_buffering=1 -d variables_order=EGPCS -d safe_mode=0 -d register_argc_argv="On" {$this->getEnvPhpIncDir()}/peclcmd.php -c {$this->getEnvPath()}/etc/pear.conf "$@"
EOD;

        $this->getFilesystem()->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "install-pear-nozlib.phar",
            $pearInstall,
            0644
        );

        $this->getFilesystem()->chdir($this->getEnvPath());

        $this->output->writeln("Installing PEAR");
        $process = $this->getProcess(
            $this->getPhpBinDir()
            . DIRECTORY_SEPARATOR . "php -n -dshort_open_tag=0 -dopen_basedir= "
            . "-derror_reporting=1803 -dmemory_limit=-1 -ddetect_unicode=0 "
            . "share" . DIRECTORY_SEPARATOR . "install-pear-nozlib.phar "
            . "-d \"".$this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "php\" -b \"bin\" -c \"etc\""
        );

        if ($process->run() != 0) {
            throw new \RuntimeException("Encountered a problem while trying to install PEAR.");
        }

        $this->getFilesystem()->remove($this->getEnvPath() . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "install-pear-nozlib.phar");

        $this->output->writeln("Saving pear.conf file.");
        $this->getFilesystem()->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "pear.conf",
            serialize($this->getPearConfigSettings()),
            0644
        );

        $this->getFilesystem()->dumpFile(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "pear",
            $pearBinSource,
            0755
        );

        $this->getFilesystem()->dumpFile(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "pecl",
            $peclBinSource,
            0755
        );
    }

    /**
     * Sets up php-config and phpize for use with the local pecl
     */
    protected function installPhpConfigPhpize()
    {
        $phpConfigPath = $this->getPhpBinDir() . DIRECTORY_SEPARATOR . "php-config";
        $phpizePath = $this->getPhpBinDir() . DIRECTORY_SEPARATOR . "phpize";
        $phpBuildIncludeDir = null;

        if (!$this->getFilesystem()->exists($phpConfigPath)) {
            $this->output->writeln("<comment>Could not find php-config in {$this->getPhpBinDir()}. You will be unable to use pecl in this virtual environment. Install the PHP development package first, and then re-run VirtPHP.</comment>");
            return;
        }

        if (!$this->getFilesystem()->exists($phpizePath)) {
            $this->output->writeln("<comment>Could not find phpize in {$this->getPhpBinDir()}. You will be unable to use pecl in this virtual environment. Install the PHP development package first, and then re-run VirtPHP.</comment>");
            return;
        }

        $phpConfigSource = $this->getFilesystem()->getContents($phpConfigPath);

        $process = $this->getProcess("{$phpConfigPath} --include-dir");
        if ($process->run() == 0) {
            $phpBuildIncludeDir = trim($process->getOutput());
        }

        // Replace prefix in php-config
        $phpConfigSource = preg_replace(
            "/^(prefix\=[^\n]+)/im",
            "\n# Old prefix value\n# $1\n" .
            "# New VirtPHP prefix value:\n" .
            "prefix=\"{$this->getEnvPath()}\"\n",
            $phpConfigSource
        );

        // Replace exec_prefix in php-config
        $phpConfigSource = preg_replace(
            "/^(exec_prefix\=[^\n]+)/im",
            "\n# Old exec_prefix value\n# $1\n" .
            "# New VirtPHP exec_prefix value:\n" .
            "exec_prefix=\"{$this->getEnvPath()}\"\n",
            $phpConfigSource
        );

        if ($phpBuildIncludeDir) {
            // Replace include_dir in php-config
            $phpConfigSource = preg_replace(
                "/^(include_dir\=[^\n]+)/im",
                "\n# Old include_dir value\n# $1\n" .
                "# New VirtPHP include_dir value:\n" .
                "include_dir=\"{$phpBuildIncludeDir}\"\n",
                $phpConfigSource
            );
        }

        // Replace extension_dir in php-config
        $phpConfigSource = preg_replace(
            "/^(extension_dir\=[^\n]+)/im",
            "\n# Old extension_dir value\n# $1\n" .
                "# New VirtPHP extension_dir value:\n" .
                "extension_dir=\"{$this->getEnvPhpExtDir()}\"\n",
            $phpConfigSource
        );

        $this->getFilesystem()->dumpFile(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "php-config",
            $phpConfigSource,
            0755
        );

        // Symlink to system phpize
        $this->getFilesystem()->symlink(
            $this->getPhpBinDir() . DIRECTORY_SEPARATOR . "phpize",
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "phpize"
        );
    }

    /**
     * Installs Composer into the new local virtual environment
     */
    protected function installComposer()
    {
        $this->output->writeln("Installing Composer locally");

        $process = $this->getProcess(
            "curl -sS https://getcomposer.org/installer | php -- --install-dir=\"{$this->getEnvBinDir()}\""
        );

        if ($process->run() != 0) {
            throw new \RuntimeException("Could not install Composer.");
        }

        $this->getFilesystem()->symlink(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "composer.phar",
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "composer"
        );
    }

    /**
     * Clones the activate script from this library and modifies for the
     * new virtual environment
     */
    protected function copyActivateScript()
    {
        $this->output->writeln("Installing activate/deactive script");

        $activateScript = $this->getFilesystem()->getContents(__DIR__ . "/../../../res/activate.sh");

        $activateScript = str_replace("__VIRTPHP_ENV_PATH__", $this->getEnvPath(), $activateScript);

        $this->getFilesystem()->dumpFile(
            $this->getEnvBinDir() . DIRECTORY_SEPARATOR . "activate",
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

    /**
     * Returns a Destroyer object for use with rolling back, in the event of an error
     *
     * @return Destroyer
     * @codeCoverageIgnore
     */
    protected function getDestroyer()
    {
        return new Destroyer($this->input, $this->output, $this->getEnvBasePath());
    }

    /**
     * Method to check to see if env json file is there and if not create it and
     * add new env with path
     */
    protected function addEnvFile()
    {
        // if not, we create it then add this environment and path
        $envPath = $_SERVER['HOME'] . DIRECTORY_SEPARATOR .  '.virtphp';
        $envFile = 'environments.json';

        if (
            !$this->getFilesystem()->exists(
                $envPath . DIRECTORY_SEPARATOR . $envFile
            )
        ) {
            $this->output->writeln(
                'Creating .virtphp directory in user home folder.'
            );
            $this->getFilesystem()->mkdir($envPath);
            $this->output->writeln(
                'Create the environments.json file.'
            );
            $this->getFilesystem()->touch(
                $envPath . DIRECTORY_SEPARATOR . $envFile
            );
        }

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
            'name' => $this->getEnvName(),
            'path' => $this->getEnvBasePath(),
        );

        // Add to final object and then write to file
        $envList[$this->getEnvName()] = $newRecord;

        $this->output->writeln(
            'Write updated list to environments file.'
        );
        $this->getFilesystem()->dumpFile(
            $envPath . DIRECTORY_SEPARATOR . $envFile,
            json_encode($envList)
        );
    }
}
