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

namespace Virtphp\Test\Util;

use Virtphp\TestCase;
use Virtphp\TestOutput;
use Virtphp\Util\EnvironmentFile;

/**
 * Filesystem test case
 */
class EnvironmentFileTest extends TestCase
{
    public $expectedEnvPath;

    public $expectedEnvFile;

    public $expectedEnvFolder;

    public $envObj;

    public $output;
    
    public function setup()
    {
        $this->expectedEnvFile = getenv('HOME')
            . DIRECTORY_SEPARATOR
            .  '.virtphp'
            . DIRECTORY_SEPARATOR
            .  'environments.json';
        $this->expectedEnvPath = getenv('HOME')
            . DIRECTORY_SEPARATOR
            . '.virtphp';
        $this->expectedEnvFolder = $this->expectedEnvPath
            . DIRECTORY_SEPARATOR
            . 'envs';

        $this->output = new TestOutput();
        $this->envObj = new EnvironmentFile($this->output);
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::__construct
     */
    public function testDirname()
    {
        $this->assertEquals($this->envObj->envFile, $this->expectedEnvFile);
        $this->assertEquals($this->envObj->envPath, $this->expectedEnvPath);
        $this->assertEquals($this->envObj->envFolder, $this->expectedEnvFolder);
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::getFilesystem
     */
    public function testGetFilesystem()
    {
        $this->envObj->getFilesystem();
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::getEnvironments
     */
    public function testGetEnvironments()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $this->assertTrue(is_array($environment->getEnvironments()));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::checkForEnvironment
     */
    public function testCheckForEnvironment()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $this->assertTrue(is_array($environment->checkForEnvironment('blueEnv')));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::checkForEnvironment
     */
    public function testCheckForEnvironmentFail()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $this->assertFalse(is_array($environment->checkForEnvironment('noEnv')));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::createEnvironmentsFile
     */
    public function testCreateEnvironmentsFile()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('exists', 'mkdir', 'touch')
        );
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('mkdir')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('touch')
            ->will($this->returnValue(true));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $this->assertFalse(is_array($environment->createEnvironmentsFile()));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::createEnvironmentsFile
     */
    public function testCreateEnvironmentsFileFailAndMake()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('getContents', 'exists', 'mkdir', 'touch')
        );
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystemMock->expects($this->any())
            ->method('mkdir')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('touch')
            ->will($this->returnValue(true));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentFile')
            ->will($this->returnValue(false));

        $environment->__construct($this->output);
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::addEnv
     */
    public function testAddEnv()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('dumpFile', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('dumpFile')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $envName = 'newEnv';
        $envPath = '/newPath/';

        $this->assertTrue($environment->addEnv($envName, $envPath));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::addEnv
     */
    public function testAddEnvNoPathAndFail()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('dumpFile', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('dumpFile')
            ->will($this->throwException(new \Exception));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $envName = 'newEnv';

        $this->assertFalse($environment->addEnv($envName));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::removeEnvFromList
     */
    public function testRemoveEnvFromList()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('dumpFile', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('dumpFile')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $envName = 'blueEnv';

        $this->assertTrue($environment->removeEnvFromList($envName));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::removeEnvFromList
     */
    public function testRemoveEnvFromListFail()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('dumpFile', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('dumpFile')
            ->will($this->returnValue(true));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $envName = 'noEnv';

        $this->assertFalse($environment->removeEnvFromList($envName));
    }

    /**
     * @covers Virtphp\Util\EnvironmentFile::removeEnvFromList
     */
    public function testRemoveEnvFromListException()
    {
        $filesystemMock = $this->getMock(
            'Virtphp\Test\Mock\FilesystemMock',
            array('dumpFile', 'getContents')
        );
        $filesystemMock->expects($this->any())
            ->method('dumpFile')
            ->will($this->throwException(new \Exception));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(
                '{"blueEnv":{"path": "/example", "name": "blueEnv"}}'
            ));

        $environment = $this->getMockBuilder('Virtphp\Util\EnvironmentFile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'createEnvironmentsFile'))
            ->getMock();
        $environment->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $environment->expects($this->any())
            ->method('createEnvironmentsFile')
            ->will($this->returnValue(true));

        $environment->__construct($this->output);

        $envName = 'blueEnv';

        $this->assertFalse($environment->removeEnvFromList($envName));
    }
}
