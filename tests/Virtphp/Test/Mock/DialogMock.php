<?php
namespace Virtphp\Test\Mock;

class DialogMock
{
    protected $confirmReturn = true;

    public function __construct($args)
    {
        if (isset($args['confirmReturn'])) {
            $this->confirmReturn = $args['confirmReturn'];
        }
    }

    public function askConfirmation($output, $question, $default)
    {
        return $this->confirmReturn;
    }
}
