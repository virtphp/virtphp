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

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * Given a string containing the path of a file or directory, this will
     * return the parent directory's path.
     *
     * @param string $path
     * @return string
     */
    public function dirname($path)
    {
        return dirname($path);
    }

    /**
     * Returns the content of the specified file
     *
     * @param string $filename
     * @param boolean $useIncludePath
     * @param resource $context
     * @param integer $offset
     *
     * @return string
     */
    public function getContents(
        $filename,
        $useIncludePath = false,
        $context = null
    ) {
        return file_get_contents(
            $filename,
            $useIncludePath,
            $context
        );
    }

    /**
     * Returns the canonicalized absolute pathname for the input $path
     *
     * @param string $path
     * @return string|boolean The absolute path or boolean FALSE on failure
     */
    public function realpath($path)
    {
        return realpath($path);
    }
}
