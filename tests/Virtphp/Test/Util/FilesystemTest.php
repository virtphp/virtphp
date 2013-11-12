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
use Virtphp\Util\Filesystem;

/**
 * Filesystem test case
 */
class FilesystemTest extends TestCase
{
    /**
     * @covers Virtphp\Util\Filesystem::dirname
     */
    public function testDirname()
    {
        $expected = dirname(__FILE__);
        $fs = new Filesystem();

        $this->assertEquals($expected, $fs->dirname(__FILE__));
    }

    /**
     * @covers Virtphp\Util\Filesystem::getContents
     */
    public function testGetContents()
    {
        $file = 'test-filesystem.txt';
        $contents = 'foobar';

        $bytes = file_put_contents($file, $contents);
        $fs = new Filesystem();

        $this->assertFileExists($file);
        $this->assertEquals(strlen($contents), $bytes);
        $this->assertEquals($contents, $fs->getContents($file));

        $deleted = unlink($file);
        $this->assertTrue($deleted);
    }

    /**
     * @covers Virtphp\Util\Filesystem::realpath
     */
    public function testRealpath()
    {
        $expected = realpath(__FILE__);
        $fs = new Filesystem();

        $this->assertEquals($expected, $fs->realpath(__FILE__));
    }
}
