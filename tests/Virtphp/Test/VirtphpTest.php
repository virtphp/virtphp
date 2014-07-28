<?php

/*
 * This file is part of virtPHP.
 *
 * (c) Jordan Kasper <github @jakerella>
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Test;

use Virtphp\TestCase;
use Virtphp\Virtphp;

class VirtphpTest extends TestCase
{
    /**
     * @covers Virtphp\Virtphp::isValidName
     */
    public function testIsValidName()
    {
        $names = array(
            'foo-Bar_2013' => true,
            'f123bar' => true,
            'Afoobar' => true,
            '5foo' => false,
            'foo.bar' => false,
        );

        foreach ($names as $name => $expected) {
            $this->assertEquals($expected, Virtphp::isValidName($name));
        }
    }
}
