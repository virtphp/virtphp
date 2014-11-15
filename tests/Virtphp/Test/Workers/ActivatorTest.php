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

namespace Virtphp\Test\Workers;

use Virtphp\TestCase;
use Virtphp\TestOutput;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\Util\Filesystem;
use Virtphp\Workers\Shower;
use Virtphp\Test\Mock\TableMock;

class ActivatorTest extends TestCase
{
    protected $testClonedEnv = 'tests/testClonedEnv';
    protected $testOriginalEnv = 'tests/testOriginalEnv';
    protected $shower;
    protected $output;
    protected $filesystemMock;
    protected $fs;

    protected function setUp()
    {
        $this->fs = new Filesystem();

        $this->fs->mkdir($this->testOriginalEnv);
        $this->fs->mkdir($this->testClonedEnv);

        $this->output = new TestOutput();
        $this->filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('getContents', 'exists')
        );

        $this->shower = $this->getMockBuilder('Virtphp\Workers\Shower')
            ->setConstructorArgs(array($this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $this->shower->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $this->shower->expects($this->any())
            ->method('getTableHelper')
            ->will($this->returnValue(new TableMock()));
    }

    protected function tearDown()
    {
        $this->fs->remove($this->testOriginalEnv);
        $this->fs->remove($this->testClonedEnv);
    }

    /**
     * @covers Virtphp\Workers\Shower::__construct
     */
    public function testConstruct()
    {
        $shower = new Shower($this->output);
        $shower->setTableHelper(new TableMock);

        $this->assertInstanceOf('Virtphp\Workers\Shower', $shower);
        $this->assertInstanceOf(
            'Virtphp\Test\Mock\TableMock',
            $shower->getTableHelper()
        );
    }

    /**
     * @covers Virtphp\Workers\Shower::execute
     * @covers Virtphp\Workers\Shower::setTableHelper
     * @covers Virtphp\Workers\Shower::getTableHelper
     */
    public function testExecute()
    {

        $this->filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"mytest":{"name":"mytest","path":"\/Users\/virtPHP\/work\/virtphp"}}'
            ));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $this->shower->setTableHelper(new TableMock());
        $this->assertTrue($this->shower->execute());
        $this->assertCount(1, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Shower::execute
     */
    public function testExecuteFailedRealPath()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('exists', 'realpath', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('realpath')
            ->will($this->returnValue(false));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"mytest":'
                . '{"name":"mytest",'
                . '"path":"\/Users\/Kite\/work\/virtphp"},'
                . '"myenv":{"name":"myenv","path":"\/users\/Kite\/work\/theKit"}}'
            ));

        $shower = $this->getMockBuilder('Virtphp\Workers\Shower')
            ->setConstructorArgs(array($this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $shower->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertTrue($shower->execute());
        $this->assertNotCount(0, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Shower::execute
     */
    public function testExecuteExceptionReturnsFalse()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('exists')
        );
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $shower = $this->getMockBuilder('Virtphp\Workers\Shower')
            ->setConstructorArgs(array($this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $shower->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertFalse($shower->execute());
        $this->assertNotCount(0, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Shower::updatePath
     */
    public function testUpdatePath()
    {

        $this->filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"mytest":{"name":"mytest","path":"\/Users\/virtPHP\/work\/virtphp"}}'
            ));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $target = 'mytest';
        $newPath = '\/Users\/Documents\/virtphp';

        $this->shower->setTableHelper(new TableMock());
        $this->assertTrue($this->shower->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            $target .' now has the path of ' . $newPath,
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Workers\Shower::updatePath
     */
    public function testUpdatePathFail()
    {

        $this->filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"mytest":{"name":"mytest","path":"\/Users\/virtPHP\/work\/virtphp"}}'
            ));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $target = 'noRecord';
        $newPath = '\/Users\/Documents\/virtphp';

        $this->shower->setTableHelper(new TableMock());
        $this->assertFalse($this->shower->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            $target .' was not found as a valid virtPHP environment.',
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Workers\Shower::updatePath
     */
    public function testUpdatePathBadPath()
    {

        $this->filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"mytest":{"name":"mytest","path":"\/Users\/virtPHP\/work\/virtphp"}}'
            ));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $this->filesystemMock->expects($this->any())
            ->method('realpath')
            ->will($this->returnValue(''));

        $target = 'noRecord';
        $newPath = '';

        $this->shower->setTableHelper(new TableMock());
        $this->assertFalse($this->shower->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            'Path provided is not an actual path.',
            $this->output->messages[0]
        );
    }
}
