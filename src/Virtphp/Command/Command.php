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

namespace Virtphp\Command;

use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Process\Process;
use Virtphp\Util\Filesystem;
use Virtphp\Util\EnvironmentFile;

class Command extends ConsoleCommand
{
    public $envFile;

    public $outpout;

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
     * Returns a Process object for executing system commands
     *
     * @param string $command The system command to run
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getProcess($command)
    {
        return new Process($command);
    }

    /**
     * Returns an instantiation of the specified worker
     *
     * @param string $worker Name of the worker
     * @param array $args Arguments to pass to the worker
     *
     * @throws \InvalidArgumentException if $worker does not exist
     * @return mixed
     */
    public function getWorker($worker, $args)
    {
        if (!class_exists($worker)) {
            $worker = "Virtphp\\Workers\\{$worker}";
            if (!class_exists($worker)) {
                throw new \InvalidArgumentException("Worker {$worker} does not exist");
            }
        }

        $reflectionObj = new \ReflectionClass($worker);

        return $reflectionObj->newInstanceArgs($args);
    }

    /**
     * Returns list of all the environments that have been created
     *
     * @return array
     */
    public function getEnvironments()
    {
        $this->setEnv();
        return $this->envFile->getEnvironments();
    }

    /**
     * Do a check for one particular environment
     *
     * @return boolean
     */
    public function checkForEnv($env)
    {
        $this->setEnv();
        return $this->envFile->checkForEnvironment($env);
    }

    /**
     * Add a new record to environments.json
     *
     * @return boolean
     */
    public function addEnv($envName, $envPath = '')
    {
        $this->setEnv();
        return $this->envFile->addEnv($envName, $envPath);
    }

    /**
     * Set Environment object
     *
     */
    public function setEnv()
    {
        if (empty($this->envFile)) {
            $this->envFile = new EnvironmentFile($this->output);
        }
    }
}
