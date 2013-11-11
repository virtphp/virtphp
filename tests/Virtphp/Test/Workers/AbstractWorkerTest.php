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

namespace Virtphp\Test\Workers;

use Virtphp\TestCase;
use Virtphp\Workers\AbstractWorker;

class AbstractWorkerTest extends TestCase
{
    protected $worker;

    protected function setUp()
    {
        $this->worker = $this->getMockForAbstractClass('Virtphp\Workers\AbstractWorker');
        $this->worker->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));
    }

    /**
     * @covers Virtphp\Workers\AbstractWorker::getFilesystem
     */
    public function testGetFilesystem()
    {
        $this->assertInstanceOf('Virtphp\\Util\\Filesystem', $this->worker->getFilesystem());
    }
}
