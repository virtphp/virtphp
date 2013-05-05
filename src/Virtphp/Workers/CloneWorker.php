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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;


class CloneWorker
{

    /** 
     * @var string
     */
    protected $original_path = array();

    /** 
     * @var string
     */
    protected $env_name;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct($original_path, $env_name, $output)
    {
        $this->original_path = $original_path;
        $this->env_name = $env_name;
        $this->filesystem = new Filesystem();
        $this->output = $output;
    }

    /**
     * Function is the guts of the worker, reading the provided
     * directory and copying those files over.
     */
    public function execute()
    {
        // Create virtphp directory
        $this->filesystem->mkdir($this->env_name);

        $full_path = realpath($this->env_name);

        // Copy over files from original directory
        $this->filesystem->mirror($this->original_path, $full_path);

        // GET activate of new directory to replace path variable
        $original_contents = file_get_contents($full_path.'/bin/activate.sh');

        // Replace V
        $new_contents = str_replace($this->original_path, $full_path, $original_contents);

        $this->filesystem->dumpFile(
            $this->env_name . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'activate.sh',
            $new_contents,
            0644
        );

        // replace in php.ini "/home/ramsey/myenv/lib/php"
        // replace in php.ini ".:/home/ramsey/myenv/share/php"
        // Pear serialized 
    }

}
