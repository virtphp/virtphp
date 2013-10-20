<?php
namespace Virtphp\Test\Mock;

class ClonerMock
{
    protected $executeReturn = true;

    public function __construct($executeReturn = true)
    {
        $this->executeReturn = $executeReturn;
    }

    public function execute()
    {
        return $this->executeReturn;
    }
}
