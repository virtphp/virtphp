<?php  namespace Virtphp\Test\Mock;
/*
 * Class : TestTable
 */

class TableMock
{
    public $headers;
    public $rows;

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function render($output)
    {
        $output->writeln(
            "table"
        );
    }
}
