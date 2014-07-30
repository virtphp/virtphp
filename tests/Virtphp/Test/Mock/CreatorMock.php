<?php
namespace Virtphp\Test\Mock;

class CreatorMock extends WorkerMock
{
    public $phpIni;
    public $pearConf;
	public $fpmConf;

	public function setCustomPhpIni($phpIni)
    {
        $this->phpIni = $phpIni;
    }

    public function setCustomPearConf($pearConf)
    {
        $this->pearConf = $pearConf;
    }

	public function setCustomFpmConf($fpmConf) {
		$this->fpmConf = $fpmConf;
	}
}
