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

namespace Virtphp\Test\Command;

use Virtphp\Command\CreateCommand;
use Virtphp\Test\Mock\CreatorMock;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class CreateCommandTest extends TestCase
{
    /**
     * @covers Virtphp\Command\CreateCommand::configure
     */
    public function testConfigure()
    {
        $command = new CreateCommand();

        $this->assertEquals('create', $command->getName());
        $this->assertEquals('Create new virtPHP environment.', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('env-name'));
        $this->assertTrue($command->getDefinition()->hasOption('php-bin-dir'));
        $this->assertTrue($command->getDefinition()->hasOption('install-path'));
        $this->assertTrue($command->getDefinition()->hasOption('php-ini'));
        $this->assertTrue($command->getDefinition()->hasOption('pear-conf'));
        $this->assertTrue($command->getDefinition()->hasOption('fpm-conf'));
    }

    /**
     * @covers Virtphp\Command\CreateCommand::execute
     */
    public function testExecute()
    {
        $filesystemMock = null;
        $creatorMock = null;

        $command = $this->getMock(
            'Virtphp\Command\CreateCommand',
            array('getFilesystem', 'getWorker', 'checkForEnv')
        );
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function () use (&$filesystemMock) {
                $filesystemMock = new FilesystemMock(false);
                return $filesystemMock;
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$creatorMock) {
                $creatorMock = new CreatorMock($name, $args);
                return $creatorMock;
            }));
        $command->expects($this->any())
            ->method('checkForEnv')
            ->will($this->returnValue(false));

        $execute = new \ReflectionMethod('Virtphp\Command\CreateCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                'foobar',
                '--php-bin-dir=/path/to/php',
                '--install-path=/example/foo/bar',
                '--php-ini=/path/to/php.ini',
                '--pear-conf=/path/to/pear.conf',
                '--fpm-conf=/path/to/fpm.conf',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertEquals('foobar', $creatorMock->args[2]);
        $this->assertEquals('/example/foo/bar', $creatorMock->args[3]);
        $this->assertEquals('/path/to/php', $creatorMock->args[4]);
        $this->assertEquals('/path/to/php.ini', $creatorMock->phpIni);
        $this->assertEquals('/path/to/pear.conf', $creatorMock->pearConf);
        $this->assertEquals('/path/to/fpm.conf', $creatorMock->fpmConf);
        $this->assertCount(4, $output->messages);
        $this->assertStringMatchesFormat(
            'Your virtual php environment (%s) has been created!',
            $output->messages[0]
        );
        $this->assertEquals(
            sprintf(
                'You can activate your new environment using: ~$ source %s/%s/bin/activate',
                $input->getOption('install-path'),
                $input->getArgument('env-name')
            ),
            trim($output->messages[1])
        );
    }

    /**
     * @covers Virtphp\Command\CreateCommand::execute
     */
    public function testExecuteWithOldPearrcFile()
    {
        $filesystemMock = null;
        $creatorMock = null;

        $command = $this->getMock('Virtphp\Command\CreateCommand',
            array('getFilesystem', 'getWorker', 'checkForEnv')
        );
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function () use (&$filesystemMock) {
                $filesystemMock = new FilesystemMock();
                return $filesystemMock;
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$creatorMock) {
                $creatorMock = new CreatorMock($name, $args);
                return $creatorMock;
            }));
        $command->expects($this->any())
            ->method('checkForEnv')
            ->will($this->returnValue(false));

        $execute = new \ReflectionMethod('Virtphp\Command\CreateCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                'foobar',
                '--php-bin-dir=/path/to/php',
                '--install-path=/example/foo/bar',
                '--php-ini=/path/to/php.ini',
                '--pear-conf=/path/to/pear.conf',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertEquals('foobar', $creatorMock->args[2]);
        $this->assertEquals('/example/foo/bar', $creatorMock->args[3]);
        $this->assertEquals('/path/to/php', $creatorMock->args[4]);
        $this->assertEquals('/path/to/php.ini', $creatorMock->phpIni);
        $this->assertEquals('/path/to/pear.conf', $creatorMock->pearConf);
        $this->assertCount(4, $output->messages);
        $this->assertContains(
            'Your virtual php environment (foobar) has been created!',
            $output->messages
        );
        $this->assertEquals(
            'You can activate your new environment using: ~$ source /example/foo/bar/foobar/bin/activate
',
            $output->messages[1]
        );
    }

    /**
     * @covers Virtphp\Command\CreateCommand::execute
     */
    public function testExecuteWithInvalidEnvName()
    {
        $command = $this->getMock('Virtphp\Command\CreateCommand',
            array('getFilesystem', 'getWorker', 'checkForEnv')
        );
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function () {
                return new FilesystemMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new CreatorMock($name, $args);
            }));
        $command->expects($this->any())
            ->method('checkForEnv')
            ->will($this->returnValue(false));

        $execute = new \ReflectionMethod('Virtphp\Command\CreateCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                '3foobar',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'Sorry, but that is not a valid environment name.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\CreateCommand::execute
     */
    public function testExecuteWithCurrentWorkingDir()
    {
        $currentWorkingDir = getenv('HOME')
            . DIRECTORY_SEPARATOR
            . '.virtphp'
            . DIRECTORY_SEPARATOR
            . 'envs';
        $filesystemMock = null;
        $creatorMock = null;

        $command = $this->getMock('Virtphp\Command\CreateCommand',
            array('getFilesystem', 'getWorker', 'checkForEnv')
        );
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function () use (&$filesystemMock) {
                $filesystemMock = new FilesystemMock();
                return $filesystemMock;
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$creatorMock) {
                $creatorMock = new CreatorMock($name, $args);
                return $creatorMock;
            }));
        $command->expects($this->any())
            ->method('checkForEnv')
            ->will($this->returnValue(false));

        $execute = new \ReflectionMethod('Virtphp\Command\CreateCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                'foobar',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertEquals('foobar', $creatorMock->args[2]);
        $this->assertEquals($currentWorkingDir, $creatorMock->args[3]);
        $this->assertNull($creatorMock->args[4]);
        $this->assertNull($creatorMock->phpIni);
        $this->assertNull($creatorMock->pearConf);
    }

    /**
     * @covers Virtphp\Command\CreateCommand::execute
     */
    public function testExecuteWithFailedCreatorExecute()
    {
        $command = $this->getMock('Virtphp\Command\CreateCommand', array('getFilesystem', 'getWorker'));
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnCallback(function () {
                return new FilesystemMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new CreatorMock($name, $args, false);
            }));

        $execute = new \ReflectionMethod('Virtphp\Command\CreateCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array(
                'file.php',
                'foobar',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
    }
}
