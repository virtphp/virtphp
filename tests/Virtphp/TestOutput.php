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

use Symfony\Component\Console\Output\Output;

class TestOutput extends Output
{
    public $messages = array();

    protected function doWrite($message, $newline)
    {
        $this->messages[] = $message;
    }
}
