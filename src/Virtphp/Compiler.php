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

namespace Virtphp;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * The Compiler class compiles virtPHP into a phar
 */
class Compiler
{
    public $testNoTokenGetAll = false;

    private $version;
    private $versionDate;

    /**
     * Compiles virtPHP into a single phar file
     *
     * @throws \RuntimeException
     * @param  string            $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'virtphp.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = $this->getProcess('git log --pretty="%H" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log.'
                . ' You must ensure to run compile from virtPHP git repository clone and that git binary is available.'
            );
        }
        $this->version = trim($process->getOutput());

        $process = $this->getProcess('git log -n1 --pretty=%ci HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log.'
                . ' You must ensure to run compile from virtPHP git repository clone and that git binary is available.'
            );
        }
        $date = new \DateTime(trim($process->getOutput()), new \DateTimeZone('UTC'));
        $this->versionDate = $date->format('Y-m-d H:i:s');

        $process = $this->getProcess('git describe --tags HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        }

        $phar = new \Phar($pharFile, 0, 'virtphp.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = $this->getFinder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__.'/..')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = $this->getFinder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->in(__DIR__.'/../../vendor/')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../res/php.ini'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../res/activate.sh'));

        $this->addVirtphpBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'), false);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }

        $content = str_replace('@package_version@', $this->version, $content);
        $content = str_replace('@release_date@', $this->versionDate, $content);

        $phar->addFromString($path, $content);
    }

    private function addVirtphpBin($phar)
    {
        $content = file_get_contents(__DIR__.'/../../bin/virtphp');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', "", $content);
        $phar->addFromString('bin/virtphp', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all') || $this->testNoTokenGetAll) {
            return $source;
        }

        $output = "";
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace("{[ \t]+}", " ", $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace("{(?:\r\n|\r|\n)}", "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace("{\n +}", "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        $stub = <<<"EOF"
#!/usr/bin/env php
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

Phar::mapPhar("virtphp.phar");

EOF;

        // add warning once the phar is older than 30 days
        if (preg_match("{^[a-f0-9]+$}", $this->version)) {
            $warningTime = time() + 30*86400;
            $stub .= "define(\"VIRTPHP_DEV_WARNING_TIME\", $warningTime);\n";
        }

        return $stub . <<<"EOF"
require "phar://virtphp.phar/bin/virtphp";

__HALT_COMPILER();
EOF;
    }

    /**
     * Returns a Finder object for finding files/dirs
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public function getFinder()
    {
        return new Finder();
    }

    /**
     * Returns a Process object for executing system commands
     *
     * @param string $command The system command to run
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getProcess($command)
    {
        return new Process($command);
    }
}
