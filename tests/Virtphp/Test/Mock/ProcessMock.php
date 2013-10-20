<?php
namespace Virtphp\Test\Mock;

class ProcessMock
{
    public $command;
    public $output = false;

    public function __construct($command, $output = false)
    {
        $this->command = $command;
        $this->output = $output;
    }

    public function run()
    {
        return true;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
