<?php
namespace Virtphp\Test\Mock;

class CreatorMock extends WorkerMock
{
    public $phpIni;
    public $pearConf;

    public function setCustomPhpIni($phpIni)
    {
        $this->phpIni = $phpIni;
    }

    public function setCustomPearConf($pearConf)
    {
        $this->pearConf = $pearConf;
    }
}
