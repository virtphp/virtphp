<?php
namespace Virtphp\Test\Mock;

class ProcessMock
{
    public $command;
    public $stdout;
    public $stderr;
    public $runReturn = 0;

    public function __construct($command, $stdout = null, $stderr = null, $runReturn = 0)
    {
        $this->command = $command;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->runReturn = $runReturn;
    }

    public function run()
    {
        return $this->runReturn;
    }

    public function getOutput()
    {
        return $this->stdout;
    }

    public function getErrorOutput()
    {
        return $this->stderr;
    }
}
