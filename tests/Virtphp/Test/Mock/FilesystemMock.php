<?php
namespace Virtphp\Test\Mock;

class FilesystemMock
{
    public $dumpFile = array();

    public function mkdir()
    {
        return true;
    }

    public function chmod()
    {
        return true;
    }

    public function mirror()
    {
        return true;
    }

    public function remove()
    {
        return true;
    }

    public function dumpFile($file, $contents, $mode = '')
    {
        $this->dumpFile[] = array($file, $contents, $mode);
        return true;
    }

    public function exists()
    {
        return true;
    }

    public function getContents()
    {
        return 'foobar';
    }

    public function realpath($path)
    {
        return $path;
    }

    public function chdir()
    {
        return true;
    }

    public function dirname($path)
    {
        return dirname($path);
    }

    public function isWritable($path)
    {
        return true;
    }

    public function symlink()
    {
        return true;
    }

    public function touch()
    {
        return true;
    }
}
