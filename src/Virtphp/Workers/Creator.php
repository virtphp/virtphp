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
     * @var array
     */
    protected $pearConfigSettings = array();

    protected static $DEFAULT_PEAR_CONFIG = array(
        'php_dir'       => '{env_path}/share/php',
        'data_dir'      => '{env_path}/share/php/data',
        'www_dir'       => '{env_path}/share/pear/www',
        'cfg_dir'       => '{env_path}/share/pear/cfg',
        'ext_dir'       => '{env_path}/lib/php',
        'doc_dir'       => '{env_path}/share/php/doc',
        'test_dir'      => '{env_path}/share/pear/tests',
        'cache_dir'     => '{env_path}/share/pear/cache',
        'download_dir'  => '{env_path}/share/pear/download',
        'temp_dir'      => '{env_path}/share/pear/temp',
        'bin_dir'       => '{env_path}/bin',
        '__channels'    => array(
            'pecl.php.net' => array('foo' => '{env_path}/bar'),
            '__uri'        => array(),
            'doc.php.net'  => array(),
        ),
        'php_bin'       => '{env_path}/bin/php',
        'php_ini'       => '{env_path}/etc/php.ini',
        'auto_discover' => 1,
    );

    /**
     * Constructs the creator worker
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
    ) {
        $this->filesystem = new Filesystem();
        $this->input = $input;
        $this->output = $output;
        $this->setEnvName($envName);
        $this->setEnvBasePath($envBasePath);

        if ($phpBinDir === null) {
            $process = new Process('which php');
            if ($process->run() == 0) {
                $phpBinDir = dirname(trim($process->getOutput()));
            } else {
                throw new \RuntimeException('Can\'t find php on the system. If php is not in the PATH, please specify its location with --php-bin-dir.');
            }
        }

        $this->setPhpBinDir($phpBinDir);
        $this->setPearConfigSettings($this->updatePearConfigSettings(self::$DEFAULT_PEAR_CONFIG));
    }

    public function getEnvName()
    {
        return $this->envName;
    }

    public function getEnvBasePath()
    {
        return $this->envBasePath;
    }

    public function getEnvPath()
    {
        return $this->getEnvBasePath() . DIRECTORY_SEPARATOR . $this->getEnvName();
    }

    public function getPhpBinDir()
    {
        return $this->phpBinDir;
    }

    public function getPearConfigSettings()
    {
        return $this->pearConfigSettings;
    }

    public function setEnvName($name)
    {
        if (!Virtphp::isValidName($name)) {
            throw new \RuntimeException("Environment name must contain only letters, numbers, dashes, and underscores. {$name} is invalid.");
        }

        $this->envName = $name;
    }

    public function setEnvBasePath($envBasePath)
    {
        $this->envBasePath = realpath($envBasePath);
    }

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
            throw new InvalidArgumentException("The 'php' file in the specified directory is not a valid php binary executable.");
        }

        $this->phpBinDir = $dir;
    }

    public function setPearConfigSettings(array $settings = array())
    {
        $this->pearConfigSettings = array_merge(self::$DEFAULT_PEAR_CONFIG, $settings);
    }

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
            $this->createPhpIni();
            $this->createPhpBinWrapper();
            $this->copyLibraries();
            $this->installPear();
            $this->installComposer();

        } catch (Exception $e) {
            $this->output->writeln("<error>ERROR: ".$e->getMessage()."</error>");
            $destroyer = new Destroyer($this->input, $this->output, $this->getEnvBasePath());
            $destroyer->execute();
            $this->output->writeln("<info>System reverted</info>");
            return false;
        }

        return true;
    }

    protected function checkEnvironment()
    {
        $this->output->writeln("<info>Checking current environment</info>");

        // if the directory exists, use it, otherwise see if it's relative
        if ($this->filesystem->exists($this->getEnvPath())) {
            throw new InvalidArgumentException("The directory for this environment already exists ({$this->getEnvPath()}).");
        } else if (!is_writable(getcwd())) {
            throw new InvalidArgumentException("The current directory is not writable, and thus we cannot create the environment.");
        }
    }

    protected function createStructure()
    {
        $this->output->writeln("<info>Creating directory structure</info>");

        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'bin');
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'etc');
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'lib');
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'php');
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'share');
        $this->filesystem->mkdir($this->getEnvPath() . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'php');
    }

    protected function createPhpIni()
    {
        $this->output->writeln("<info>Creating custom php.ini</info>");

        $phpIni = file_get_contents(__DIR__ . '/../../../res/php.ini');

        $phpIni = str_replace(
            '__VIRTPHP_ENV_PHP_INCLUDE_PATH__',
            $this->getEnvPath() . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'php',
            $phpIni
        );

        $phpIni = str_replace(
            '__VIRTPHP_ENV_PHP_EXTENSION_PATH__',
            $this->getEnvPath() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'php',
            $phpIni
        );

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'php.ini',
            $phpIni,
            0644
        );
    }

    protected function createPhpBinWrapper()
    {
        $this->output->writeln("<info>Wrapping PHP binary</info>");

        $phpBinWrapper = <<<EOD
#!/bin/bash
{$this->getPhpBinDir()}/php -c {$this->getEnvPath()}/etc/php.ini "$@"
EOD;

        $this->filesystem->dumpFile(
            $this->getEnvPath() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'php',
            $phpBinWrapper,
            0755
        );
    }

    protected function copyLibraries()
    {
        $this->output->writeln("<info>Copying other libraries</info>");
    }

    protected function installPear()
    {
        $this->output->writeln("<info>Installing PEAR locally</info>");
        // TODO: download PEAR phar
        //       run without user prompt (see notes in google doc)
        //       generate PEAR config
        //       run various commands to get pear and pecl working
    }

    protected function installComposer()
    {
        $this->output->writeln("<info>Installing Composer locally</info>");
    }

    protected function updatePearConfigSettings(array $pearConfig = array())
    {
        foreach ($pearConfig as $key => &$value) {
            if (is_array($value)) {
                $value = $this->updatePearConfigSettings($value);
            }
            if (is_string($value)) {
                $value = str_replace('{env_path}', $this->getEnvPath(), $value);
            }
        }

        return $pearConfig;
    }
}
