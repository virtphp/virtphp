<?php
namespace Virtphp\Test\Mock;

class WorkerMock
{
    public $name;
    public $args = array();
    public $executeReturn = true;

    public function __construct($name, $args = array(), $executeReturn = true)
    {
        $this->name = $name;
        $this->args = $args;
        $this->executeReturn = $executeReturn;
    }

    public function execute()
    {
        return $this->executeReturn;
    }
}
