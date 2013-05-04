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


class Creator
{

    /**
     * @var stirng
     */
    private $rootPath = ".";
    /**
     * @var string
     */
    private $phpBinary = null;
    /**
     * @var array
     */
    private $pearConfigSettings = array();
    

    private static $DEFAULT_PEAR_CONFIG = array(
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


    public function __construct($rootPath = ".") {
      $this->setRootPath($rootPath);
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
            
            $this->createStructure();
            $this->createPhpIni();
            $this->createPhpBinWrapper();
            $this->copyLibraries();
            $this->installPear();
            $this->installComposer();
            $this->createActivateScript();
            $this->createDeactivateScript();

        } catch (Exception $e) {
            // TODO: need to destroy our environment if any part of setup failed
        }
    }

    protected function createStructure() {
      
    }

    protected function createPhpIni() {
        // TODO: copy php ini from VirtPHP to env structure
        //       change paths as necessary
    }

    protected function createPhpBinWrapper() {
      
    }

    protected function copyLibraries() {

    }

    protected function installPear() {
        // TODO: download PEAR phar
        //       run without user prompt (see notes in google doc)
        //       generate PEAR config
        //       run various commands to get pear and pecl working 
    }

    protected function installComposer() {

    }

    protected function createActivateScript() {

    }

    protected function createDeactivateScript() {

    }


}
