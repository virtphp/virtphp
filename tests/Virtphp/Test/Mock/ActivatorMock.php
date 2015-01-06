<?php
namespace Virtphp\Test\Mock;

class ActivatorMock extends WorkerMock
{
    public function getOs()
    {
        return 'Darwin';
    }

    public function copyToClipboard($source)
    {
        return true;
    }
}
