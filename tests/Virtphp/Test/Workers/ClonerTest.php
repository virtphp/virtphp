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
use Virtphp\Workers\Cloner;

class ClonerTest extends TestCase
{
    protected $testClonedEnv = 'testClonedEnv';
    protected $testOriginalEnv = 'tests/testOriginalEnv';
    protected $cloner;
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

        $this->cloner = $this->getMockBuilder('Virtphp\Workers\Cloner')
            ->setConstructorArgs(array($this->testOriginalEnv, $this->testClonedEnv, $this->output))
            ->setMethods(array('getFilesystem'))
            ->getMock();
        $this->cloner->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
    }

    protected function tearDown()
    {
        $this->fs->remove($this->testOriginalEnv);
        $this->fs->remove($this->testClonedEnv);
    }

    /**
     * @covers Virtphp\Workers\Cloner::__construct
     */
    public function testConstruct()
    {
        $cloner = new Cloner($this->testOriginalEnv, $this->testClonedEnv, $this->output);

        $this->assertInstanceOf('Virtphp\Workers\Cloner', $cloner);
    }

    /**
     * @covers Virtphp\Workers\Cloner::execute
     * @covers Virtphp\Workers\Cloner::cloneEnv
     * @covers Virtphp\Workers\Cloner::updateActivateFile
     * @covers Virtphp\Workers\Cloner::updatePhpIni
     * @covers Virtphp\Workers\Cloner::createPhpBinWrapper
     * @covers Virtphp\Workers\Cloner::sourcePear
     * @covers Virtphp\Workers\Cloner::processConfigSettings
     * @covers Virtphp\Workers\Cloner::addEnvToFile
     */
    public function testExecute()
    {
        $this->markTestIncomplete(
            'The Cloner is broken, so we need to fix and then fix tests.'
        );
        $fromPath = realpath($this->testOriginalEnv);
        $toPath = $this->testClonedEnv;

        $fromPathShare = $fromPath . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'php';
        $fromPathLib = $fromPath . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'php';
        $fromPhpIni = $fromPath . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'php.ini';
        $fromPearConfig = array(
            'foo' => "{$fromPath}/bar/baz",
            'bar' => "{$fromPath}/qux/quux",
            'baz' => array(
                'qux' => "{$fromPath}/corge/grault",
                'quux' => array(
                    'corge' => "{$fromPath}/garply/waldo",
                ),
            ),
        );

        $toPathShare = $toPath . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'php';
        $toPathLib = $toPath . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'php';
        $toPhpIni = $toPath . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'php.ini';
        $toPearConfig = array(
            'foo' => "{$toPath}/bar/baz",
            'bar' => "{$toPath}/qux/quux",
            'baz' => array(
                'qux' => "{$toPath}/corge/grault",
                'quux' => array(
                    'corge' => "{$toPath}/garply/waldo",
                ),
            ),
        );

        $this->filesystemMock->expects($this->any())
            ->method('getContents')
            ->will($this->onConsecutiveCalls(
                "\$activateFilePath: Test activate script {$fromPath} foo bar baz",
                "\$iniPHPLocation: share path: {$fromPathShare}, lib path: {$fromPathLib}",
                "exec /usr/local/bin/php -c {$fromPhpIni} \"\$@\"",
                serialize($fromPearConfig)
            ));
        $this->filesystemMock->expects($this->any())
            ->method('exists')
            ->will($this->onConsecutiveCalls(false, true));

        $this->assertTrue($this->cloner->execute());
        $this->assertNotCount(0, $this->output->messages);
        $this->assertNotCount(0, $this->filesystemMock->dumpFile);
        $this->assertEquals(
            $toPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'activate.sh',
            $this->filesystemMock->dumpFile[0][0]
        );
        $this->assertEquals(
            "\$activateFilePath: Test activate script {$toPath} foo bar baz",
            $this->filesystemMock->dumpFile[0][1]
        );
        $this->assertEquals(
            $toPath . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'php.ini',
            $this->filesystemMock->dumpFile[1][0]
        );
        $this->assertEquals(
            "\$iniPHPLocation: share path: {$toPathShare}, lib path: {$toPathLib}",
            $this->filesystemMock->dumpFile[1][1]
        );
        $this->assertEquals(
            $toPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'php',
            $this->filesystemMock->dumpFile[2][0]
        );
        $this->assertEquals(
            "exec /usr/local/bin/php -c {$toPhpIni} \"\$@\"",
            $this->filesystemMock->dumpFile[2][1]
        );
        $this->assertEquals(
            $toPath . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'pear.conf',
            $this->filesystemMock->dumpFile[3][0]
        );
        $this->assertEquals(
            serialize($toPearConfig),
            $this->filesystemMock->dumpFile[3][1]
        );
        $this->assertEquals(
            "Cloning virtPHP env from {$fromPath} to {$toPath}",
            $this->output->messages[0]
        );
        $this->assertEquals(
            "Copying contents of {$fromPath} to {$toPath}",
            $this->output->messages[1]
        );
        $this->assertEquals(
            'Updating activate file.',
            $this->output->messages[2]
        );
        $this->assertEquals(
            'Updating PHP ini file.',
            $this->output->messages[3]
        );
        $this->assertEquals(
            'Updating PHP bin wrapper.',
            $this->output->messages[4]
        );
        $this->assertEquals(
            'Updating virtual PEAR install and config',
            $this->output->messages[5]
        );
        $this->assertEquals(
            'Setting proper permissions on cloned bin directory',
            $this->output->messages[6]
        );
    }

    /**
     * @covers Virtphp\Workers\Cloner::execute
     */
    public function testExecuteExceptionReturnsFalse()
    {
        $this->markTestIncomplete(
            'The Cloner is broken, so we need to fix and then fix tests.'
        );
        $fromPath = realpath($this->testOriginalEnv);
        $toPath = realpath($this->testClonedEnv);

        $cloner = $this->getMockBuilder('Virtphp\Workers\Cloner')
            ->setConstructorArgs(array($this->testOriginalEnv, $this->testClonedEnv, $this->output))
            ->setMethods(array('getFilesystem', 'cloneEnv'))
            ->getMock();
        $cloner->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $cloner->expects($this->any())
            ->method('cloneEnv')
            ->will($this->throwException(new \Exception()));

        $this->assertFalse($cloner->execute());
        $this->assertEquals(
            "Cloning virtPHP env from {$fromPath} to {$toPath}",
            $this->output->messages[0]
        );
        $this->assertEquals(
            'Error: cloning directory failed.',
            $this->output->messages[1]
        );
    }
}
