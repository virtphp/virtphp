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

use Symfony\Component\Console\Input\ArgvInput;
use Virtphp\Command\DestroyCommand;
use Virtphp\TestCase;
use Virtphp\TestOutput;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\Workers\Destroyer;

class DestroyerTest extends TestCase
{
    protected $destroyer;
    protected $input;
    protected $output;

    protected function setUp()
    {
        $command = new DestroyCommand();
        $this->input = new ArgvInput(
            array(
                'file.php',
                '/path/to/virtphp/project',
            ),
            $command->getDefinition()
        );
        $this->output = new TestOutput();

        $this->destroyer = $this->getMockBuilder('Virtphp\Workers\Destroyer')
            ->setConstructorArgs(array($this->input, $this->output, '/path/to/virtphp/project'))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $this->destroyer->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue(new FilesystemMock()));
    }

    /**
     * @covers Virtphp\Workers\Destroyer::__construct
     */
    public function testConstruct()
    {
        $destroyer = new Destroyer($this->input, $this->output, '/foo/bar');

        $this->assertInstanceOf('Virtphp\Workers\Destroyer', $destroyer);
    }

    /**
     * @covers Virtphp\Workers\Destroyer::getRootPath
     */
    public function testGetRootPath()
    {
        $this->assertEquals('/path/to/virtphp/project', $this->destroyer->getRootPath());
    }

    /**
     * @covers Virtphp\Workers\Destroyer::setRootPath
     */
    public function testSetRootPath()
    {
        $this->assertEmpty($this->destroyer->setRootPath('/path/to/foo'));
        $this->assertEquals('/path/to/foo', $this->destroyer->getRootPath());
    }

    /**
     * @covers Virtphp\Workers\Destroyer::execute
     * @covers Virtphp\Workers\Destroyer::removeStructure
     * @covers Virtphp\Workers\Destroyer::removeFromList
     */
    public function testExecute()
    {
        $this->assertTrue($this->destroyer->execute());
        $this->assertEquals(
            'Removing directory structure',
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Workers\Destroyer::execute
     */
    public function testExecuteRootPathNotExists()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('exists'));
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $destroyer = $this->getMockBuilder('Virtphp\Workers\Destroyer')
            ->setConstructorArgs(array($this->input, $this->output, '/path/to/virtphp/project'))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $destroyer->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertFalse($destroyer->execute());
        $this->assertEquals(
            'This directory does not exist!',
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Workers\Destroyer::execute
     */
    public function testExecuteInvalidVirtphpEnvironment()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('exists'));
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->onConsecutiveCalls(true, false));

        $destroyer = $this->getMockBuilder('Virtphp\Workers\Destroyer')
            ->setConstructorArgs(array($this->input, $this->output, '/path/to/virtphp/project'))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $destroyer->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertFalse($destroyer->execute());
        $this->assertEquals(
            'This directory does not contain a valid VirtPHP environment!',
            $this->output->messages[0]
        );
    }
}
