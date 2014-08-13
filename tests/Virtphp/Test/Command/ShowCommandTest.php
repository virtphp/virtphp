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
use Virtphp\Command\ShowCommand;
use Virtphp\Test\Mock\ShowerMock;
use Virtphp\Test\Mock\FilesystemMock;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class ShowCommandTest extends TestCase
{
    /**
     * @covers Virtphp\Command\ShowCommand::configure
     */
    public function testConfigure()
    {
        $show = new ShowCommand();

        $this->assertEquals('show', $show->getName());
        $this->assertEquals(
            'Show a list of all the current environments.',
            $show->getDescription()
        );
        $this->assertTrue($show->getDefinition()->hasOption('env'));
        $this->assertTrue($show->getDefinition()->hasOption('path'));
    }

    /**
     * @covers Virtphp\Command\ShowCommand::execute
     */
    public function testExecute()
    {
        $command = $this->getMock(
            'Virtphp\Command\ShowCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ShowerMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ShowCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array(),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\ShowCommand::execute
     */
    public function testExecuteEnvNoPath()
    {
        $command = $this->getMock(
            'Virtphp\Command\ShowCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ShowerMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ShowCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array('', '--env=testEnv'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertStringMatchesFormat(
            'You must provide both an environment name and path to resync.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\ShowCommand::execute
     */
    public function testExecutePathNoEnv()
    {
        $command = $this->getMock(
            'Virtphp\Command\ShowCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ShowerMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ShowCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array('', '--path=/testEnv/'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertStringMatchesFormat(
            'You must provide both an environment name and path to resync.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\ShowCommand::execute
     */
    public function testExecuteWithPathEnv()
    {
        $command = $this->getMock(
            'Virtphp\Command\ShowCommand', array('getWorker')
        );
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnCallback(function ($name, $args) {
                return new ShowerMock($name, $args);
            }));

        $execute = new \ReflectionMethod(
            'Virtphp\Command\ShowCommand',
            'execute'
        );
        $execute->setAccessible(true);

        $input = new ArgvInput(
            array('', '--path=/testEnv/', '--env=envName'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertCount(1, $output->messages);
        $this->assertStringMatchesFormat(
            'Environment updated to new path.',
            $output->messages[0]
        );
    }
}
