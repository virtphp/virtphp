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
use Virtphp\Util\ErrorHandler;

/**
 * ErrorHandler test case
 */
class ErrorHandlerTest extends TestCase
{
    protected $screamSetting;

    protected function setUp()
    {
        $this->screamSetting = ini_get('xdebug.scream');
    }

    protected function tearDown()
    {
        // Return the scream setting back to its original setting
        // so that other tests are not affected by this
        ini_set('xdebug.scream', $this->screamSetting);
    }

    /**
     * Test ErrorHandler handles notices
     */
    public function testErrorHandlerCaptureNotice()
    {
        $this->setExpectedException('\ErrorException', 'Undefined index: baz');

        ErrorHandler::register();

        $array = array('foo' => 'bar');
        $array['baz'];
    }

    /**
     * Test ErrorHandler handles warnings
     */
    public function testErrorHandlerCaptureWarning()
    {
        $this->setExpectedException('\ErrorException', 'array_merge');

        ErrorHandler::register();

        array_merge(array(), 'string');
    }

    /**
     * Test ErrorHandler handles warnings
     */
    public function testErrorHandlerRespectsAtOperator()
    {
        ErrorHandler::register();

        @trigger_error('test', E_USER_NOTICE);
    }

    /**
     * Test ErrorHandler includes xdebug.scream message
     */
    public function testErrorHandlerXdebugScream()
    {
        ini_set('xdebug.scream', 1);
        $this->setExpectedException(
            '\ErrorException',
            'array_merge(): Argument #2 is not an array'
            . "\n\nWarning: You have xdebug.scream enabled, the warning above may be"
            . "\na legitimately suppressed error that you were not supposed to see."
        );

        ErrorHandler::register();

        @array_merge(array(), 'string');
    }
}
