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

use Virtphp\Command\CreateCommand;
use Virtphp\TestCase;
use Virtphp\TestOutput;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\Test\Mock\ProcessMock;
use Virtphp\Util\Filesystem;
use Virtphp\Workers\Creator;

class CreatorTest extends TestCase
{
    protected $command;
    protected $input;
    protected $output;
    protected $dumbCreator;

    protected function setUp()
    {
        $this->command = new CreateCommand();

        $this->input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                'foobar',
                '--php-bin-dir=/path/to/php',
                '--install-path=/example/foo/bar',
                '--php-ini=/path/to/php.ini',
                '--pear-conf=/path/to/pear.conf',
            ),
            $this->command->getDefinition()
        );

        $this->output = new TestOutput();

        $this->dumbCreator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $this->dumbCreator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue(new FilesystemMock()));
        $this->dumbCreator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command);
            }));
    }

    /**
     * @covers Virtphp\Workers\Creator::__construct
     */
    public function testConstruct()
    {
        $this->assertEmpty($this->dumbCreator->__construct($this->input, $this->output, 'myenv', '/foo/bar', '/path/to/php'));
        $this->assertInstanceOf('Virtphp\\Workers\\Creator', $this->dumbCreator);
        $this->assertEquals('myenv', $this->dumbCreator->getEnvName());
        $this->assertEquals('/foo/bar', $this->dumbCreator->getEnvBasePath());
        $this->assertEquals('/path/to/php', $this->dumbCreator->getPhpBinDir());

        $pearConfig = $this->dumbCreator->getPearConfigSettings();
        $this->assertInternalType('array', $pearConfig);
        $this->assertArrayHasKey('php_dir', $pearConfig);
        $this->assertEquals('/foo/bar/myenv/share/php', $pearConfig['php_dir']);
    }

    /**
     * @covers Virtphp\Workers\Creator::__construct
     */
    public function testConstructWithNullPhpBinDir()
    {
        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue(new FilesystemMock()));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command, '/foo/system/path/to/php');
            }));

        $this->assertEmpty($creator->__construct($this->input, $this->output, 'myenv', '/foo/bar'));
        $this->assertInstanceOf('Virtphp\\Workers\\Creator', $creator);
        $this->assertEquals('myenv', $creator->getEnvName());
        $this->assertEquals('/foo/bar', $creator->getEnvBasePath());
        $this->assertEquals('/foo/system/path/to', $creator->getPhpBinDir());

        $pearConfig = $creator->getPearConfigSettings();
        $this->assertInternalType('array', $pearConfig);
        $this->assertArrayHasKey('php_dir', $pearConfig);
        $this->assertEquals('/foo/bar/myenv/share/php', $pearConfig['php_dir']);
    }

    /**
     * @covers Virtphp\Workers\Creator::__construct
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can't find php on the system. If php is not in the PATH, please specify its location with --php-bin-dir.
     */
    public function testConstructIsUnableToFindPhp()
    {
        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue(new FilesystemMock()));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command, false, 1);
            }));

        $creator->__construct($this->input, $this->output, 'myenv', '/foo/bar');
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvName
     * @covers Virtphp\Workers\Creator::setEnvName
     */
    public function testGetSetEnvName()
    {
        $this->assertEmpty($this->dumbCreator->setEnvName('foobar'));
        $this->assertEquals('foobar', $this->dumbCreator->getEnvName());
    }

    /**
     * @covers Virtphp\Workers\Creator::setEnvName
     * @expectedException RuntimeException
     * @expectedExceptionMessage Environment name must contain only letters, numbers, dashes, and underscores.
     */
    public function testSetEnvNameWithInvalidName()
    {
        $this->dumbCreator->setEnvName('Tom O\'Reilly');
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvBasePath
     * @covers Virtphp\Workers\Creator::setEnvBasePath
     */
    public function testGetSetEnvBasePath()
    {
        $this->assertEmpty($this->dumbCreator->setEnvBasePath('/my/env/base/path'));
        $this->assertEquals('/my/env/base/path', $this->dumbCreator->getEnvBasePath());
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvPath
     */
    public function testGetEnvPath()
    {
        $this->dumbCreator->setEnvBasePath('/foo/env/path');
        $this->dumbCreator->setEnvName('myenv2');

        $this->assertEquals('/foo/env/path/myenv2', $this->dumbCreator->getEnvPath());
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvBinDir
     */
    public function testGetEnvBinDir()
    {
        $this->dumbCreator->setEnvBasePath('/foo/env/path');
        $this->dumbCreator->setEnvName('myenv2');

        $this->assertEquals('/foo/env/path/myenv2/bin', $this->dumbCreator->getEnvBinDir());
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvPhpExtDir
     */
    public function testGetEnvPhpExtDir()
    {
        $this->dumbCreator->setEnvBasePath('/foo/env/path');
        $this->dumbCreator->setEnvName('myenv2');

        $this->assertEquals('/foo/env/path/myenv2/lib/php', $this->dumbCreator->getEnvPhpExtDir());
    }

    /**
     * @covers Virtphp\Workers\Creator::getEnvPhpIncDir
     */
    public function testGetEnvPhpIncDir()
    {
        $this->dumbCreator->setEnvBasePath('/foo/env/path');
        $this->dumbCreator->setEnvName('myenv2');

        $this->assertEquals('/foo/env/path/myenv2/share/php', $this->dumbCreator->getEnvPhpIncDir());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPhpIni
     * @covers Virtphp\Workers\Creator::setCustomPhpIni
     */
    public function testGetSetCustomPhpIni()
    {
        $this->assertEmpty($this->dumbCreator->setCustomPhpIni(null));
        $this->assertNull($this->dumbCreator->getCustomPhpIni());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPhpIni
     * @covers Virtphp\Workers\Creator::setCustomPhpIni
     */
    public function testGetSetCustomPhpIniWithNonNullValue()
    {
        $this->assertEmpty($this->dumbCreator->setCustomPhpIni('/path/to/php.ini'));
        $this->assertEquals('/path/to/php.ini', $this->dumbCreator->getCustomPhpIni());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPhpIni
     * @covers Virtphp\Workers\Creator::setCustomPhpIni
     */
    public function testGetSetCustomPhpIniWithFalseValue()
    {
        $this->assertEmpty($this->dumbCreator->setCustomPhpIni(false));
        $this->assertNull($this->dumbCreator->getCustomPhpIni());
    }

    /**
     * @covers Virtphp\Workers\Creator::getPhpBinDir
     * @covers Virtphp\Workers\Creator::setPhpBinDir
     */
    public function testGetSetPhpBinDir()
    {
        $this->assertEmpty($this->dumbCreator->setPhpBinDir('/path/to/php'));
        $this->assertEquals('/path/to/php', $this->dumbCreator->getPhpBinDir());
    }

    /**
     * @covers Virtphp\Workers\Creator::setPhpBinDir
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The specified php bin directory does not exist.
     */
    public function testSetPhpBinDirWhenDirNotExists()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('exists'));
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $creator->setPhpBinDir('/some/path/to/php/that/does/not/exist');
    }

    /**
     * @covers Virtphp\Workers\Creator::setPhpBinDir
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no php binary in the specified directory.
     */
    public function testSetPhpBinDirWhenBinaryNotExists()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('exists'));
        $filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->onConsecutiveCalls(true, false));

        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $creator->setPhpBinDir('/some/path/to/php/that/does/not/exist');
    }

    /**
     * @covers Virtphp\Workers\Creator::setPhpBinDir
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "php" file in the specified directory is not a valid php binary executable.
     */
    public function testSetPhpBinDirWhenBinaryNotValid()
    {
        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue(new FilesystemMock()));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command, false, 1);
            }));

        $creator->setPhpBinDir('/some/path/to/php/that/is/not/valid');
    }

    /**
     * @covers Virtphp\Workers\Creator::getPearConfigSettings
     * @covers Virtphp\Workers\Creator::setPearConfigSettings
     */
    public function testGetSetPearConfigSettings()
    {
        $class = new \ReflectionClass('Virtphp\\Workers\\Creator');
        $property = $class->getProperty('DEFAULT_PEAR_CONFIG');
        $property->setAccessible(true);
        $pearConfig = $property->getValue();

        $settings = array(
            'foo' => 'bar',
            'baz' => array('qux' => 'quux'),
            'php_dir' => '/path/to/php/dir',
        );

        $expected = array_merge($pearConfig, $settings);

        $this->assertEmpty($this->dumbCreator->setPearConfigSettings($settings));
        $this->assertEquals($expected, $this->dumbCreator->getPearConfigSettings());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPearConf
     * @covers Virtphp\Workers\Creator::setCustomPearConf
     */
    public function testGetSetCustomPearConfWithNullValue()
    {
        $this->assertEmpty($this->dumbCreator->setCustomPearConf());
        $this->assertNull($this->dumbCreator->getCustomPearConf());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPearConf
     * @covers Virtphp\Workers\Creator::setCustomPearConf
     */
    public function testGetSetCustomPearConfWithFalseValue()
    {
        $this->assertEmpty($this->dumbCreator->setCustomPearConf(false));
        $this->assertNull($this->dumbCreator->getCustomPearConf());
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPearConf
     * @covers Virtphp\Workers\Creator::setCustomPearConf
     */
    public function testGetSetCustomPearConf()
    {
        $class = new \ReflectionClass('Virtphp\\Workers\\Creator');
        $property = $class->getProperty('DEFAULT_PEAR_CONFIG');
        $property->setAccessible(true);
        $pearConfig = $property->getValue();

        $settings = array(
            'foo' => 'bar',
            'baz' => array('qux' => 'quux'),
            'php_dir' => '/path/to/php/dir',
        );

        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('getContents'));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(serialize($settings)));

        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command);
            }));

        $creator->__construct($this->input, $this->output, 'myenv', '/foo/bar', '/path/to/php');

        // Replace {env_path} in the PEAR config with the values we want to see
        $method = new \ReflectionMethod('Virtphp\\Workers\\Creator', 'updatePearConfigSettings');
        $method->setAccessible(true);
        $pearConfig = $method->invoke($creator, $pearConfig);
        $expected = array_merge($settings, $pearConfig);

        $this->assertEmpty($creator->setCustomPearConf('/path/to/pear.conf'));
        $this->assertEquals('/path/to/pear.conf', $creator->getCustomPearConf());
        $this->assertEquals($expected, $creator->getPearConfigSettings());
        $this->assertEquals('Getting custom pear.conf info from /path/to/pear.conf', $this->output->messages[0]);
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPearConf
     * @covers Virtphp\Workers\Creator::setCustomPearConf
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to unserialize custom PEAR config file
     */
    public function testSetCustomPearConfWithInvalidPearConf()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('getContents'));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue('foobar'));

        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command);
            }));

        $creator->__construct($this->input, $this->output, 'myenv', '/foo/bar', '/path/to/php');

        $creator->setCustomPearConf('/path/to/custom/pear.conf');
    }

    /**
     * @covers Virtphp\Workers\Creator::getCustomPearConf
     * @covers Virtphp\Workers\Creator::setCustomPearConf
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to get contents of custom PEAR config file
     */
    public function testSetCustomPearConfUnableToGetContents()
    {
        $filesystemMock = $this->getMock('Virtphp\Test\Mock\FilesystemMock', array('getContents'));
        $filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(false));

        $creator = $this->getMockBuilder('Virtphp\Workers\Creator')
            ->disableOriginalConstructor()
            ->setMethods(array('getFilesystem', 'getProcess'))
            ->getMock();
        $creator->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));
        $creator->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command);
            }));

        $creator->__construct($this->input, $this->output, 'myenv', '/foo/bar', '/path/to/php');

        $creator->setCustomPearConf('/path/to/custom/pear.conf');
    }

    /**
     * @covers Virtphp\Workers\Creator::updatePearConfigSettings
     */
    public function testUpdatePearConfigSettings()
    {
        $settings = array(
            'foo' => '{env_path}/bar',
            'baz' => array('qux' => '{env_path}/quux'),
            'php_dir' => '{env_path}/dir',
        );

        $expectedSettings = array(
            'foo' => '/path/to/testenv/bar',
            'baz' => array('qux' => '/path/to/testenv/quux'),
            'php_dir' => '/path/to/testenv/dir',
        );

        $this->dumbCreator->setEnvName('testenv');
        $this->dumbCreator->setEnvBasePath('/path/to');

        // Replace {env_path} in the PEAR config with the values we want to see
        $method = new \ReflectionMethod('Virtphp\\Workers\\Creator', 'updatePearConfigSettings');
        $method->setAccessible(true);
        $updatedConfig = $method->invoke($this->dumbCreator, $settings);

        $this->assertEquals($expectedSettings, $updatedConfig);
    }
}
