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

namespace Virtphp;

class Virtphp
{
    const VERSION = '@package_version@';

    /**
     * Function to make sure provided
     * environment name is valid.
     *
     * @param string $envName
     */
    public static function isValidName($envName)
    {
        if (preg_match('/^[a-zA-Z][0-9a-zA-Z_\-]*$/', $envName)) {
            return true;
        }

        return false;
    }
}
