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
use Virtphp\Command\ActivateCommand;
use Virtphp\Test\Mock\ActivatorMock;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class ActivateCommandTest extends TestCase
{
    /**
     * @covers Virtphp\Command\ActivateCommand::configure
     */
    public function testConfigure()
    {
        $show = new ActivateCommand();

        $this->assertEquals('activate', $show->getName());
        $this->assertEquals(
            'Returns the activate file path to be sourced for '
            . 'the specified environment.',
            $show->getDescription()
        );
        $this->assertTrue($show->getDefinition()->hasArgument('env'));
    }

    /**
     * @covers Virtphp\Command\ActivateCommand::execute
     */
    public function testExecute()
    {
        $command = $this->getMock(
            'Virtphp\Command\ActivateCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ActivatorMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ActivateCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(
                'activate',
                'envName'
            ),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\ActivateCommand::execute
     */
    public function testExecuteFail()
    {
        $command = $this->getMock(
            'Virtphp\Command\ActivateCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ActivatorFailMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ActivateCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array('action', 'name'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\ActivateCommand::execute
     */
    public function testExecuteEnvNoEnvironmentName()
    {
        $command = $this->getMock(
            'Virtphp\Command\ActivateCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ActivatorMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ActivateCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array('', ''),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertStringMatchesFormat(
            'No environment name was provided.',
            $output->messages[0]
        );
    }
}

class ActivatorFailMock
{
    public function __construct($output, $envName)
    {
        
    }

    public function execute()
    {
        return false;
    }
}
