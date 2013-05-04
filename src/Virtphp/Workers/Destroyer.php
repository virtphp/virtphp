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

namespace Virtphp\Workers;


class Destroyer
{

    /**
     * @var stirng
     */
    private $rootPath;


    public function __construct($rootPath = ".") {
        $this->setRootPath($rootPath);
    }


    public function getRootPath() { return $this->rootPath; }
    public function setRootPath($path = ".") {
        $this->rootPath = strval($path);
    }


    public function execute() {

        $this->removeStructure();
        // TODO: what else?
    }

    protected function removeStructure() {
        // TODO: remove all created folders
    }

}