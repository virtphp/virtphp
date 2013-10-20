<?php
namespace Virtphp\Test\Mock;

class HelperSetMock
{
    protected $args = array();

    public function __construct($args = array())
    {
        $this->args = $args;
    }

    public function get($helper)
    {
        switch ($helper) {
            case 'dialog':
                return new DialogMock($this->args);
        }

        return null;
    }
}
