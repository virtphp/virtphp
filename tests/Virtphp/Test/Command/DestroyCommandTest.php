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

use Symfony\Component\Console\Input\ArgvInput;
use Virtphp\Command\DestroyCommand;
use Virtphp\Test\Mock\HelperSetMock;
use Virtphp\Test\Mock\DestroyerMock;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class DestroyCommandTest extends TestCase
{
    /**
     * @covers Virtphp\Command\DestroyCommand::configure
     */
    public function testConfigure()
    {
        $command = new DestroyCommand();

        $this->assertEquals('destroy', $command->getName());
        $this->assertEquals('Destroy an existing virtual environment.', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('env-path'));
    }

    /**
     * @covers Virtphp\Command\DestroyCommand::execute
     */
    public function testExecute()
    {
        $destroyerMock = null;

        $command = $this->getMock('Virtphp\Command\DestroyCommand', array('getHelperSet', 'getWorker', 'getEnvironments'));
        $command->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnCallback(function () {
                return new HelperSetMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$destroyerMock) {
                $destroyerMock = new DestroyerMock($name, $args);
                return $destroyerMock;
            }));
        $command->expects($this->any())
            ->method('getEnvironments')
            ->will($this->returnValue(
                array(
                    '/path/to/virtphp/project' => array(
                        'name' => '',
                        'path' => '',
                    )
                )
            ));

        $execute = new \ReflectionMethod('Virtphp\Command\DestroyCommand', 'execute');
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'file.php',
                '/path/to/virtphp/project',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertEquals('//path/to/virtphp/project', $destroyerMock->args[2]);
        $this->assertCount(4, $output->messages);
        $this->assertStringMatchesFormat(
            'Your virtual PHP environment has been destroyed.',
            $output->messages[0]
        );
        $this->assertStringMatchesFormat(
            'We deleted the contents of: //path/to/virtphp/project',
            $output->messages[1]
        );
    }

    /**
     * @covers Virtphp\Command\DestroyCommand::execute
     */
    public function testExecuteWithActiveVirtphpEnv()
    {
        // Create directory for testing
        $dir = __DIR__ . '/footest';
        if (!file_exists($dir) && !mkdir($dir)) {
            $this->markTestSkipped('Unable to create a directory for testing');
        }

        putenv("VIRTPHP_ENV_PATH={$dir}");
        $destroyerMock = null;

        $command = $this->getMock('Virtphp\Command\DestroyCommand', array('getHelperSet', 'getWorker', 'getEnvironments'));
        $command->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnCallback(function () {
                return new HelperSetMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$destroyerMock) {
                $destroyerMock = new DestroyerMock($name, $args);
                return $destroyerMock;
            }));
        $command->expects($this->any())
            ->method('getEnvironments')
            ->will($this->returnValue(
                array(
                    $dir => array(
                        'name' => '',
                        'path' => '',
                    )
                )
            ));

        $execute = new \ReflectionMethod('Virtphp\Command\DestroyCommand', 'execute');
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'file.php',
                $dir,
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'You must deactivate this virtual environment before destroying it!',
            $output->messages[0]
        );

        // Clean up created directory
        rmdir($dir);
    }

    /**
     * @covers Virtphp\Command\DestroyCommand::execute
     */
    public function testExecuteWithCanceledConfirmation()
    {
        $destroyerMock = null;

        $command = $this->getMock('Virtphp\Command\DestroyCommand', array('getHelperSet', 'getWorker', 'getEnvironments'));
        $command->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnCallback(function () {
                return new HelperSetMock(array('confirmReturn' => false));
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$destroyerMock) {
                $destroyerMock = new DestroyerMock($name, $args);
                return $destroyerMock;
            }));
        $command->expects($this->any())
            ->method('getEnvironments')
            ->will($this->returnValue(
                array(
                    '/path/to/virtphp/project' => array(
                        'name' => '',
                        'path' => '',
                    )
                )
            ));

        $execute = new \ReflectionMethod('Virtphp\Command\DestroyCommand', 'execute');
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'file.php',
                '/path/to/virtphp/project',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'This action has been canceled.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\DestroyCommand::execute
     */
    public function testExecuteWithFailedDestroyerExecution()
    {
        $destroyerMock = null;

        $command = $this->getMock('Virtphp\Command\DestroyCommand', array('getHelperSet', 'getWorker', 'getEnvironments'));
        $command->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnCallback(function () {
                return new HelperSetMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$destroyerMock) {
                $destroyerMock = new DestroyerMock($name, $args, false);
                return $destroyerMock;
            }));
        $command->expects($this->any())
            ->method('getEnvironments')
            ->will($this->returnValue(
                array(
                    '/path/to/virtphp/project' => array(
                        'name' => '',
                        'path' => '',
                    )
                )
            ));

        $execute = new \ReflectionMethod('Virtphp\Command\DestroyCommand', 'execute');
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'file.php',
                '/path/to/virtphp/project',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
    }

    /**
     * @covers Virtphp\Command\DestroyCommand::execute
     */
    public function testExecuteWhereHasNotBeenCreated()
    {
        $destroyerMock = null;

        $command = $this->getMock('Virtphp\Command\DestroyCommand', array('getHelperSet', 'getWorker', 'getEnvironments'));
        $command->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnCallback(function () {
                return new HelperSetMock();
            }));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) use (&$destroyerMock) {
                $destroyerMock = new DestroyerMock($name, $args, false);
                return $destroyerMock;
            }));
        $command->expects($this->any())
            ->method('getEnvironments')
            ->will($this->returnValue(array()));

        $execute = new \ReflectionMethod('Virtphp\Command\DestroyCommand', 'execute');
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'file.php',
                '/path/to/virtphp/project',
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
    }
}
