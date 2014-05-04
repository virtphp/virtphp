<?php
namespace Virtphp\Test\Mock;

class ShowerMock extends WorkerMock
{
    public function updatePath($envName, $updatePath)
    {
        return true;
    }
}
