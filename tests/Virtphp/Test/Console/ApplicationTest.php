<?php
namespace Virtphp\Test\Console;

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

use Symfony\Component\Console\Input\ArgvInput;
use Virtphp\Console\Application;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class ApplicationTest extends TestCase
{
    protected $app;
    protected $input;
    protected $output;

    protected function setUp()
    {
        $this->app = $this->getMock(
            'Virtphp\Console\Application',
            array(
                'parentDoRun',
                'parentRun',
            )
        );

        $this->app->expects($this->any())
            ->method('parentDoRun')
            ->will($this->returnValue(0));

        $this->app->expects($this->any())
            ->method('parentRun')
            ->will($this->returnValue(0));

        Application::$testPhpVersion = false;

        $this->input = new ArgvInput();
        $this->output = new TestOutput();
    }

    /**
     * @covers Virtphp\Console\Application::__construct
     */
    public function testConstruct()
    {
        date_default_timezone_set('UTC');
        $app = new Application();

        $this->assertInstanceOf('Virtphp\\Console\\Application', $app);
        $this->assertEquals('virtPHP', $app->getName());
        $this->assertEquals('@package_version@', $app->getVersion());
        $this->assertEmpty(ini_get('xdebug.show_exception_trace'));
        $this->assertEmpty(ini_get('xdebug.scream'));
        $this->assertEquals('UTC', date_default_timezone_get());
    }

    /**
     * @covers Virtphp\Console\Application::run
     */
    public function testRun()
    {
        $this->assertEquals(0, $this->app->run());
        $this->assertEquals(0, $this->app->run($this->input, $this->output));
    }

    /**
     * @covers Virtphp\Console\Application::doRun
     */
    public function testDoRun()
    {
        $result = $this->app->doRun($this->input, $this->output);

        $this->assertCount(0, $this->output->messages);
        $this->assertEquals(0, $result);
    }

    /**
     * @covers Virtphp\Console\Application::doRun
     */
    public function testDoRunWithLesserPhpVersion()
    {
        Application::$testPhpVersion = true;

        $result = $this->app->doRun($this->input, $this->output);

        $this->assertCount(1, $this->output->messages);
        $this->assertStringMatchesFormat(
            '<warning>'
            . 'virtPHP only officially supports PHP 5.3.3 and above, '
            . 'you will most likely encounter problems with your PHP %s, upgrading is strongly recommended.'
            . '</warning>',
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Console\Application::doRun
     */
    public function testDoRunWithOutdatedVirtphp()
    {
        define('VIRTPHP_DEV_WARNING_TIME', time() - 31*3600*24);
        $result = $this->app->doRun($this->input, $this->output);

        $this->assertCount(1, $this->output->messages);
        $this->assertStringMatchesFormat(
            '<warning>'
            . 'Warning: This development build of virtPHP is over 30 days old. '
            . 'It is recommended to update it by running "%s self-update" to get the latest version.'
            . '</warning>',
            $this->output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Console\Application::getHelp
     */
    public function testGetHelp()
    {
        $this->assertInternalType('string', $this->app->getHelp());
    }

    /**
     * @covers Virtphp\Console\Application::getDefaultCommands
     */
    public function testGetDefaultCommands()
    {
        $getDefaultCommands = new \ReflectionMethod(
            'Virtphp\Console\Application',
            'getDefaultCommands'
        );
        $getDefaultCommands->setAccessible(true);

        $commands = $getDefaultCommands->invoke($this->app);

        $this->assertInternalType('array', $commands);

        foreach ($commands as $command) {
            $this->assertInstanceOf('Symfony\\Component\\Console\\Command\\Command', $command);
        }
    }

    /**
     * @covers Virtphp\Console\Application::getLongVersion
     */
    public function testGetLongVersion()
    {
        $this->assertInternalType('string', $this->app->getLongVersion());
    }
}
