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

namespace Virtphp\Test\Command;

use Virtphp\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class CommandTest extends TestCase
{
    protected $command;

    protected function setUp()
    {
        $this->command = new Command('Foo');
        $this->command->output = new TestOutput();
    }

    /**
     * @covers Virtphp\Command\Command::getFilesystem()
     */
    public function testGetFilesystem()
    {
        $this->assertInstanceOf(
            'Virtphp\\Util\\Filesystem',
            $this->command->getFilesystem()
        );
    }

    /**
     * @covers Virtphp\Command\Command::getProcess()
     */
    public function testGetProcess()
    {
        $this->assertInstanceOf(
            'Symfony\\Component\\Process\\Process',
            $this->command->getProcess('ls .')
        );
    }

    /**
     * @covers Virtphp\Command\Command::getWorker
     */
    public function testGetWorkerWithFullyQualifiedName()
    {
        $input = new ArgvInput();
        $output = new TestOutput();
        $rootPath = '/example/foo/bar/baz/d7c4fec4-392c-11e3-9a96-ce3f5508acd9';

        $this->assertInstanceOf(
            'Virtphp\\Workers\\Destroyer',
            $this->command->getWorker(
                'Virtphp\\Workers\\Destroyer',
                array($input, $output, $rootPath)
            )
        );
    }

    /**
     * @covers Virtphp\Command\Command::getWorker
     */
    public function testGetWorkerWithShortName()
    {
        $input = new ArgvInput();
        $output = new TestOutput();
        $rootPath = '/example/foo/bar/baz/d7c4fec4-392c-11e3-9a96-ce3f5508acd9';

        $this->assertInstanceOf(
            'Virtphp\\Workers\\Destroyer',
            $this->command->getWorker(
                'Destroyer',
                array($input, $output, $rootPath)
            )
        );
    }

    /**
     * @covers Virtphp\Command\Command::getWorker
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Worker Virtphp\Workers\Foobar does not exist
     */
    public function testGetWorker()
    {
        $this->command->getWorker('Foobar', array(1, 2, 3));
    }

    /**
     * @covers Virtphp\Command\Command::getEnvironments
     */
    public function testGetEnvironments()
    {
        $this->command->getEnvironments();
        $this->assertNotEmpty($this->command->envFile);
    }
}
