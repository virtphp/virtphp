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

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Factory
{
    public static function createAdditionalStyles()
    {
        return array(
            "highlight" => new OutputFormatterStyle("red"),
            "warning" => new OutputFormatterStyle("black", "yellow"),
        );
    }
}
