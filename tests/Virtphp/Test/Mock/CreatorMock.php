<?php
namespace Virtphp\Test\Mock;

class CreatorMock
{
    public $name;
    public $args = array();
    public $phpIni;
    public $pearConf;
    public $executeReturn = true;

    public function __construct($name, $args, $executeReturn = true)
    {
        $this->name = $name;
        $this->args = $args;
        $this->executeReturn = $executeReturn;
    }

    public function setCustomPhpIni($phpIni)
    {
        $this->phpIni = $phpIni;
    }

    public function setCustomPearConf($pearConf)
    {
        $this->pearConf = $pearConf;
    }

    public function execute()
    {
        return $this->executeReturn;
    }
}
