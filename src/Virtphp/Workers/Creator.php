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
use Virtphp\Workers\Destroyer;
use InvalidArgumentException;


class Creator
{

    /**
     * @var InputInterface
     */
    protected $input = null;
    /**
     * @var OutputInterface
     */
    protected $output = null;
    /**
     * @var string
     */
    protected $rootPath = ".";
    /**
     * @var string
     */
    protected $phpBinary = null;
    /**
     * @var array
     */
    protected $pearConfigSettings = array();
    

    protected static $DEFAULT_PEAR_CONFIG = array(
        'php_dir'       => '/{base_path}/{env_name}/share/php',
        'data_dir'      => '/{base_path}/{env_name}/share/php/data',
        'www_dir'       => '/{base_path}/{env_name}/share/pear/www',
        'cfg_dir'       => '/{base_path}/{env_name}/share/pear/cfg',
        'ext_dir'       => '/{base_path}/{env_name}/lib/php',
        'doc_dir'       => '/{base_path}/{env_name}/share/php/doc',
        'test_dir'      => '/{base_path}/{env_name}/share/pear/tests',
        'cache_dir'     => '/{base_path}/{env_name}/share/pear/cache',
        'download_dir'  => '/{base_path}/{env_name}/share/pear/download',
        'temp_dir'      => '/{base_path}/{env_name}/share/pear/temp',
        'bin_dir'       => '/{base_path}/{env_name}/bin',
        '__channels'    => array(
            'pecl.php.net' => array(),
            '__uri'        => array(),
            'doc.php.net'  => array(),
        ),
        'php_bin'       => '/{base_path}/{env_name}/bin/php',
        'php_ini'       => '/{base_path}/{env_name}/etc/php.ini',
        'auto_discover' => 1,
    );


    public function __construct(InputInterface $input, OutputInterface $output, $rootPath = ".", $binary = null) {
        $this->input = $input;
        $this->output = $output;
        $this->setRootPath(strval($rootPath));
        if (!$binary) {
            // TODO: determine php binary location
        }
        $this->setPhpBinary($binary);
    }
    
    
    public function getRootPath() { return $this->rootPath; }
    public function getPhpBinary() { return $this->phpBinary; }
    public function getPearConfigSettings() { return $this->prearConfigSettings; }

    public function setRootPath($path = ".") {
        $this->rootPath = strval($path);
    }

    public function setPhpBinary($path = ".") {
        $this->phpBinary = strval($path);
    }

    public function setPearConfigSettings(array $settings) {
        $this->pearConfigSettings = array_merge(self::$DEFAULT_PEAR_CONFIG, $settings);
    }

    
    public function execute() {
        
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
            $destroyer = new Destroyer($this->input, $this->output, $this->rootPath);
            $destroyer->execute();
            $this->output->writeln("<info>System reverted</info>");
            return false;
        }

        return true;
    }

    protected function checkEnvironment() {
        $this->output->writeln("<info>Checking current environment</info>");
        // if the directory exists, use it, otherwise see if it's relative
        if (file_exists($this->rootPath)) {
            throw new InvalidArgumentException("The directory for this environment already exists ({$this->rootPath})");
        } else if (!is_writable(getcwd())) {
            throw new InvalidArgumentException("The current directory is not writable, and thus we cannot create the environment");
        }

    }

    protected function createStructure() {
        $this->output->writeln("<info>Creating directory structure</info>");
    }

    protected function createPhpIni() {
        $this->output->writeln("<info>Creating custom php.ini</info>");
        // TODO: copy php ini from VirtPHP to env structure
        //       change paths as necessary
    }

    protected function createPhpBinWrapper() {
        $this->output->writeln("<info>Wrapping PHP binary</info>");
    }

    protected function copyLibraries() {
        $this->output->writeln("<info>Copying other libraries</info>");
    }

    protected function installPear() {
        $this->output->writeln("<info>Installing PEAR locally</info>");
        // TODO: download PEAR phar
        //       run without user prompt (see notes in google doc)
        //       generate PEAR config
        //       run various commands to get pear and pecl working 
    }

    protected function installComposer() {
        $this->output->writeln("<info>Installing Composer locally</info>");
    }

}
