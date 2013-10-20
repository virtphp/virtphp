<?php
namespace Virtphp\Test\Mock;

class ProcessMock
{
    public $command;
    public $output = false;
    public $runReturn = 0;

    public function __construct($command, $output = false, $runReturn = 0)
    {
        $this->command = $command;
        $this->output = $output;
        $this->runReturn = $runReturn;
    }

    public function run()
    {
        return $this->runReturn;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
