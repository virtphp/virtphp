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
use Virtphp\Workers\Activator;
use Virtphp\Test\Mock\TableMock;
use Virtphp\Util\EnvironmentFile;

class ActivatorTest extends TestCase
{
    protected $testClonedEnv = 'tests/testClonedEnv';
    protected $testOriginalEnv = 'tests/testOriginalEnv';
    protected $activator;
    protected $output;
    protected $filesystemMock;
    protected $fs;
    protected $envFile; 

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
        $this->envFile = $this->getMock(
            'Virtphp\Util\EnvironmentFile',
            array('getFileSystem', 'getEnvironments', 'checkForEnvironment', 'createEnvironmentsFile', 'addEnv', 'removeEnvFromList'),
            array($this->output),
            '',
            false
        );

        $this->envFile->envContents = array(
            'envName' => array(
                'name' => 'envName',
                'path' => '\/Users\/Kite\/.virtphp\/envs',
            )
        );

        $this->envFile->expects($this->any())
            ->method('checkForEnvironment')
            ->will($this->returnValue(''));

        $this->activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getFilesystem', 'getOs', 'copyToClipboard'))
            ->getMock();
        $this->activator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(true));
    }

    protected function tearDown()
    {
        $this->fs->remove($this->testOriginalEnv);
        $this->fs->remove($this->testClonedEnv);
    }

    /**
     * @covers Virtphp\Workers\Activator::__construct
     */
    public function testConstruct()
    {
        $activator = new Activator($this->output, 'envName', $this->envFile);

        $this->assertInstanceOf('Virtphp\Workers\Activator', $activator);
        // for some reason assertNotEquals was not found.
        $this->assertNotEquals(false, $activator->getOs());
        $this->assertFalse(false, $activator->copyToClipboard('TestEnv'));
    }

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    public function testExecute()
    {

        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $this->assertTrue($this->activator->execute());
        $this->assertCount(1, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    /*public function testExecuteFailedRealPath()
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

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $activator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertTrue($activator->execute());
        $this->assertNotCount(0, $this->output->messages);
    }*/

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    /*public function testExecuteExceptionReturnsFalse()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('exists')
        );
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $activator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->assertFalse($activator->execute());
        $this->assertNotCount(0, $this->output->messages);
    }*/

    /**
     * @covers Virtphp\Workers\Activator::updatePath
     */
    /*public function testUpdatePath()
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

        $this->activator->setTableHelper(new TableMock());
        $this->assertTrue($this->activator->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            $target .' now has the path of ' . $newPath,
            $this->output->messages[0]
        );
    }*/

    /**
     * @covers Virtphp\Workers\Activator::updatePath
     */
    /*public function testUpdatePathFail()
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

        $this->activator->setTableHelper(new TableMock());
        $this->assertFalse($this->activator->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            $target .' was not found as a valid virtPHP environment.',
            $this->output->messages[0]
        );
    }*/

    /**
     * @covers Virtphp\Workers\Activator::updatePath
     */
    /*public function testUpdatePathBadPath()
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

        $this->activator->setTableHelper(new TableMock());
        $this->assertFalse($this->activator->updatePath($target, $newPath));
        $this->assertCount(1, $this->output->messages);
        $this->assertEquals(
            'Path provided is not an actual path.',
            $this->output->messages[0]
        );
    }*/
}
