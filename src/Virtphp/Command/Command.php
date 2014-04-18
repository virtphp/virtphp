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

class Command extends ConsoleCommand
{
    /**
     * Returns a Filesystem object for executing filesystem operations
     *
     * @return Virtphp\Util\Filesystem
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
     * @return Symfony\Component\Process\Process
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
}
