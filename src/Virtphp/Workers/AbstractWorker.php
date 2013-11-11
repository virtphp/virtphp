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

use Virtphp\Util\Filesystem;

abstract class AbstractWorker
{
    /**
     * @var Virtphp\Util\Filesystem
     */
    protected $filesystem;

    /**
     * Executes the worker, performing the primary job of the worker
     *
     * @return boolean Whether the action was successful
     */
    abstract public function execute();

    /**
     * Returns a filesystem object for use with operations in this class
     *
     * @return Virtphp\Util\Filesystem
     */
    public function getFilesystem()
    {
        if ($this->filesystem === null) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }
}
