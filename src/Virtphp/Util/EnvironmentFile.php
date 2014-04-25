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

    public function __construct()
    {
        // set the path to the environments folder
        $this->envFile = getenv('HOME') . DIRECTORY_SEPARATOR .  '.virtphp' . DIRECTORY_SEPARATOR .  'environments.json';
        $this->envPath = getenv('HOME') . DIRECTORY_SEPARATOR .  '.virtphp';
        $this->envFolder = $this->envPath . DIRECTORY_SEPARATOR . 'envs';
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
        // Get the contents of the environments.json file
        $envContents = $this->getFilesystem()->getContents($this->envFile);
        // Convert the contents to array
        return json_decode($envContents, true);
    }
}
