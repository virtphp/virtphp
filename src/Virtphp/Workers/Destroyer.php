<?php
/*
 * This file is part of virtPHP.
 *
 * (c) Jordan Kasper <github @jakerella>
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Workers;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;

class Destroyer extends AbstractWorker
{

    /**
     * @var InputInterface
     */
    protected $input = null;
    /**
     * @var OutputInterface
     */
    protected $output = null;
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param null $rootPath Path to root
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        $rootPath = null
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->setRootPath($rootPath);
    }

    /**
     * getRootPath
     *
     * @return string Root path
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * setRootPath
     *
     * @param string $path Path to root
     */
    public function setRootPath($path = ".")
    {
        $this->rootPath = strval($path);
    }

    /**
     * Function is the guts of the worker, reading the provided
     * options and destroying the old virtual env.
     *
     * @return boolean Whether or not the action was successful
     */
    public function execute()
    {

        if (!$this->getFilesystem()->exists($this->rootPath)) {
            $this->output->writeln('<error>This directory does not exist!</error>');

            return false;
        }

        if (!$this->getFilesystem()->exists($this->rootPath.DIRECTORY_SEPARATOR.'.virtphp')) {
            $this->output->writeln('<error>This directory does not contain a valid virtPHP environment, and as such will not be removed!</error>');

            return false;
        }

        $this->removeStructure();

        return true;
    }

    /**
     * Removes the directory structure for the old virtual env
     */
    protected function removeStructure()
    {
        $this->output->writeln('<info>Removing directory structure</info>');
        $this->getFilesystem()->remove($this->rootPath);
    }
}
