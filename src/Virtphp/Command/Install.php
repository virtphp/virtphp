<?php

/*
 * This file is part of VirtPHP.
 *
 * (c) Jordan Kasper <github @jakerella> 
 *     Jacques Woodcock <github @jwoodcock> 
 *     Ben Ramsey <github @ramsey>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{

    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Create new virtphp from scratch.')
    }

    /* Function to process input options for command.
     *
     * @param string $input
     * @param string $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check default locations for valid PHP
        //
        // Setup environment
    }

}
