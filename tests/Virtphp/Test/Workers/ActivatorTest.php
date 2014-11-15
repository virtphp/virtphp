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

use Virtphp\Command\ActivateCommand;
use Virtphp\TestCase;
use Virtphp\TestOutput;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\Util\Filesystem;
use Virtphp\Workers\Activator;
use Virtphp\Test\Mock\TableMock;
use Virtphp\Util\EnvironmentFile;
use Virtphp\Test\Mock\ProcessMock;

class ActivatorTest extends TestCase
{
    protected $testClonedEnv = 'tests/testClonedEnv';
    protected $testOriginalEnv = 'tests/testOriginalEnv';
    protected $activator;
    protected $output;
    protected $filesystemMock;
    protected $fs;
    protected $envFile; 
    protected $command;

    protected function setUp()
    {
        $this->command = new ActivateCommand();

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

        $this->activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getFilesystem', 'getOs', 'copyToClipboard', 'getProcess'))
            ->getMock();
        $this->activator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
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
        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(true));

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

        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(true));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $this->envFile->expects($this->any())
            ->method('checkForEnvironment')
            ->will($this->returnValue($this->envFile->envContents['envName']));

        $this->assertTrue($this->activator->execute());
        $this->assertCount(8, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    public function testExecuteFileExistsFail()
    {

        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(true));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));
        $this->envFile->expects($this->any())
            ->method('checkForEnvironment')
            ->will($this->returnValue(''));

        $this->assertFalse($this->activator->execute());
        $this->assertCount(1, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    public function testExecuteFail()
    {

        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(true));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $this->envFile->expects($this->any())
            ->method('checkForEnvironment')
            ->will($this->returnValue(''));

        $this->assertFalse($this->activator->execute());
        $this->assertCount(1, $this->output->messages);
    }

    /**
     * @covers Virtphp\Workers\Activator::execute
     */
    public function testExecuteClipboardFail()
    {

        $this->activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue(true));
        $this->activator->expects($this->any())
            ->method('copyToClipboard')
            ->will($this->returnValue(false));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $this->envFile->expects($this->any())
            ->method('checkForEnvironment')
            ->will($this->returnValue($this->envFile->envContents['envName']));

        $this->assertTrue($this->activator->execute());
        $this->assertEquals('Could not copy the path to your clipboard. Please copy the instructions below.', $this->output->messages[0]);
    }

    /**
     * @covers Virtphp\Workers\Activator::getOs
     */
    public function testGetOs()
    {
        $process = new ProcessMock('uname', 'jwoodcock', 0);

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getProcess'))
            ->getMock();

        $activator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $this->assertEquals('jwoodcock', $activator->getOs());
    }

    /**
     * @covers Virtphp\Workers\Activator::getOs
     */
    public function testGetOsFail()
    {
        $process = new ProcessMock('uname', 'jwoodcock', 1);

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getProcess'))
            ->getMock();

        $activator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $this->assertEquals('error', $activator->getOs());
    }

    /**
     * @covers Virtphp\Workers\Activator::copyToClipboard
     */
    public function testCopyToClipboardDarwin()
    {
        $process = new ProcessMock('uname', 'jwoodcock', 0);

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getProcess', 'getOs'))
            ->getMock();

        $activator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));
        $activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue('Darwin'));

        $this->assertTrue($activator->copyToClipboard('not important'));
    }

    /**
     * @covers Virtphp\Workers\Activator::copyToClipboard
     */
    public function testCopyToClipboardLinux()
    {
        $process = new ProcessMock('uname', 'jwoodcock', 0);

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getProcess', 'getOs'))
            ->getMock();

        $activator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));
        $activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue('Linux'));

        $this->assertTrue($activator->copyToClipboard('not important'));
    }

    /**
     * @covers Virtphp\Workers\Activator::copyToClipboard
     */
    public function testCopyToClipboardFail()
    {
        $process = new ProcessMock('uname', 'jwoodcock', 0);

        $activator = $this->getMockBuilder('Virtphp\Workers\Activator')
            ->setConstructorArgs(array($this->output, 'envName', $this->envFile))
            ->setMethods(array('getProcess', 'getOs'))
            ->getMock();

        $activator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));
        $activator->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue('FailOs'));

        $this->assertFalse($activator->copyToClipboard('not important'));
    }
}
