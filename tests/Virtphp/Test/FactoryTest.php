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

namespace Virtphp\Test;

use Virtphp\TestCase;
use Virtphp\Factory;

class FactoryTest extends TestCase
{
    /**
     * @covers Virtphp\Factory::createAdditionalStyles
     */
    public function testCreateAdditionalStyles()
    {
        $this->assertInternalType('array', Factory::createAdditionalStyles());

        foreach (Factory::createAdditionalStyles() as $k => $v) {
            $this->assertInstanceOf(
                'Symfony\Component\Console\Formatter\OutputFormatterStyle',
                $v
            );
        }
    }
}
