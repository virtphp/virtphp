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

error_reporting(E_ALL);
date_default_timezone_set("America/Chicago");
ini_set('xdebug.scream', 0);

$loader = require __DIR__."/../src/bootstrap.php";
$loader->addPsr4('Virtphp\\', __DIR__ . '/Virtphp/');
