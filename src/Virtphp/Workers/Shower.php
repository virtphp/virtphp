<?php

/**
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Shower extends AbstractWorker
{

    /**
     * @var string
     */
    protected $envPath;

    /**
     * @var string
     */
    protected $file = 'environments.json';

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $tableHelper;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructs the shower worker
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->envPath = getenv('HOME') . DIRECTORY_SEPARATOR . '.virtphp';
        $this->filePath = $this->envPath . DIRECTORY_SEPARATOR. $this->file;
        $this->output = $output;
        $this->tableHelper = new TableHelper();
    }

    /**
     * Function is the guts of the worker, reading the provided
     * directory and copying those files over.
     *
     * @return boolean Whether or not the action was successful
     */
    public function execute()
    {
        // If a resync was not requested, let's check to make sure we have
        // a valid json file to read and read it
        if ($this->getFilesystem()->exists($this->filePath)) {

            // get list of environments and convert to array
            $envList = json_decode(
                $this->getFileSystem()->getContents($this->filePath),
                true
            );
            $tableValues = array();
            $envWarnings = array();

            // convert to table formatted rows
            foreach ($envList as $key => $value) {
                // get path to source
                $sourceBase = $value['path']
                    . DIRECTORY_SEPARATOR
                    . $key;
                $sourcePath = $sourceBase
                    . DIRECTORY_SEPARATOR
                    . 'bin'
                    . DIRECTORY_SEPARATOR
                    . 'activate';
                $warning = '';
                // verify if path is real
                if (!$this->getFilesystem()->realpath($sourceBase . DIRECTORY_SEPARATOR . '.virtphp')) {
                    $warning = '(!)';
                    $envWarnings[] = $key;
                }
                $tableValues[] = array(
                    'name' => $warning . $key,
                    'path' => $value['path'],
                    'source' => 'source ' . $sourcePath
                );
            }

            // build table
            $this->getTableHelper()->setHeaders(
                array('Name', 'Path', 'ACTIVATE')
            )->setRows($tableValues);
            // render table
            $this->getTableHelper()->render($this->output);

            if (count($envWarnings) > 0) {
                for ($x = 0; $x < count($envWarnings); $x++) {
                    $this->output->writeln('');
                    $this->output->writeln(
                        '<comment>WARNING: The `' . $envWarnings[$x] . '`'
                        . ' virtPHP environment can no longer be found. Perhaps'
                        . ' it has moved or has been deleted.</comment>'
                    );
                    $this->output->writeln('');
                }
            }

        } else {
            $this->output->writeln(
                '<error>either no environments have been created on this system'
                . ' or the json file has been moved</error>'
            );
            return false;
        }

        return true;
    }

    /**
     * Method that takes a provided environment name and path and resyncs the
     * record to the udpated path.
     *
     * @param $envName
     * @param $updatedPath
     * @return boolean Weather or not the action was successful
     */
    public function updatePath($envName, $updatedPath)
    {
        // get real path
        $realPath = $this->getFilesystem()->realpath($updatedPath);

        if (!$realPath) {
            $this->output->writeln(
                '<error>Path provided is not an actual path.</error>'
            );
            return false;
        }

        // get list of environments and convert to array
        $envList = json_decode(
            $this->getFileSystem()->getContents($this->filePath),
            true
        );

        if (!isset($envList[$envName])) {
            $this->output->writeln(
                '<error>' . $envName . ' was not found as a valid virtPHP'
                . ' environment.</error>'
            );
            return false;
        }

        $envList[$envName]['path'] = $realPath;

        $this->getFilesystem()->dumpFile(
            $this->filePath,
            json_encode($envList)
        );

        $this->output->writeln(
            '<bg=green;options=bold>'
            . $envName
            . ' now has the path of '
            . $updatedPath
            . '</bg=green;options=bold>'
        );
        return true;
    }

    /**
     * Method for returning the table object.
     *
     * @return object Table helper object
     */
    public function getTableHelper()
    {
        return $this->tableHelper;
    }

    /**
     * Method for setting the table object.
     *
     */
    public function setTableHelper($table)
    {
        $this->tableHelper = $table;
    }
}
