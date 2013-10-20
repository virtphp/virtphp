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

namespace Virtphp\Test;

use Virtphp\Test\Mock\ProcessMock;
use Virtphp\TestCase;
use Virtphp\Compiler;

class CompilerTest extends TestCase
{
    protected $testPhar = 'virtphpCompilerTestPhar.phar';

    protected function setUp()
    {
        if (file_exists($this->testPhar)) {
            unlink($this->testPhar);
        }
    }

    protected function tearDown()
    {
        if (file_exists($this->testPhar)) {
            unlink($this->testPhar);
        }
    }

    /**
     * @covers Virtphp\Compiler::getFinder
     * @covers Virtphp\Compiler::getProcess
     */
    public function testGetFinderGetProcess()
    {
        $compiler = new Compiler();

        $this->assertInstanceOf('Symfony\\Component\\Finder\\Finder', $compiler->getFinder());
        $this->assertInstanceOf('Symfony\\Component\\Process\\Process', $compiler->getProcess('ls .'));
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     */
    public function testCompile()
    {
        $compiler = new Compiler();
        $compiler->compile($this->testPhar);

        $this->assertTrue(file_exists($this->testPhar));
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     */
    public function testCompileWithExistingFile()
    {
        touch($this->testPhar);

        $compiler = new Compiler();
        $compiler->compile($this->testPhar);

        $this->assertTrue(file_exists($this->testPhar));
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     */
    public function testCompileNoTokenGetAll()
    {
        $compiler = new Compiler();
        $compiler->testNoTokenGetAll = true;
        $compiler->compile($this->testPhar);

        $this->assertTrue(file_exists($this->testPhar));
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can't run git log. You must ensure to run compile from virtphp git repository clone and that git binary is available.
     */
    public function testCompileGitLogException()
    {
        $compiler = $this->getMock('Virtphp\Compiler', array('getProcess'));
        $compiler->expects($this->any())
            ->method('getProcess')
            ->will($this->returnCallback(function($command) {
                return new ProcessMock($command, false, -1);
            }));

        $compiler->compile($this->testPhar);
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can't run git log. You must ensure to run compile from virtphp git repository clone and that git binary is available.
     */
    public function testCompileGitLogExceptionPartDeux()
    {
        $process = $this->getMockBuilder('Virtphp\Test\Mock\ProcessMock', array('run'))
            ->disableOriginalConstructor()
            ->getMock();
        $process->expects($this->any())
            ->method('run')
            ->will($this->onConsecutiveCalls(0, -1));

        $compiler = $this->getMock('Virtphp\Compiler', array('getProcess'));
        $compiler->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $compiler->compile($this->testPhar);
    }

    /**
     * @covers Virtphp\Compiler::compile
     * @covers Virtphp\Compiler::addFile
     * @covers Virtphp\Compiler::addVirtphpBin
     * @covers Virtphp\Compiler::stripWhitespace
     * @covers Virtphp\Compiler::getStub
     */
    public function testCompileGitDescribeTags()
    {
        $process = $this->getMockBuilder('Virtphp\Test\Mock\ProcessMock', array('run', 'getOutput'))
            ->disableOriginalConstructor()
            ->getMock();
        $process->expects($this->any())
            ->method('run')
            ->will($this->returnValue(0));
        $process->expects($this->any())
            ->method('getOutput')
            ->will($this->onConsecutiveCalls(
                '3645455baa3520ee54c539c62c59aeae36a9933d',
                '2013-09-17 15:34:06 -0700',
                '2.4.0-9-g3645455'
            ));

        $compiler = $this->getMock('Virtphp\Compiler', array('getProcess'));
        $compiler->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $compiler->compile($this->testPhar);

        $this->assertTrue(file_exists($this->testPhar));

        $cmdOutput = null;
        exec("php {$this->testPhar} -V", $cmdOutput);

        // The expected string reads "VirtPHP version 2.4.0-9-g3645455 2013-09-17 22:34:06"
        // but since it has console coloring, it comes out as a binary string,
        // so we have it base64-encoded for testing
        $expected = base64_decode('G1szMm1WaXJ0UEhQG1swbSB2ZXJzaW9uIBtbMzNtMi40LjAtOS1nMzY0NTQ1NRtbMG0gMjAxMy0wOS0xNyAyMjozNDowNg==');
        $actual = $cmdOutput[0];

        $this->assertEquals($expected, $actual);
    }
}
