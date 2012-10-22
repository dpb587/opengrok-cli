<?php

/**
 * (c) Danny Berger <dpb587@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPB\OpenGrokCLI;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Based on https://github.com/composer/composer/blob/master/src/Composer/Compiler.php
 */
class Compiler
{
    public function compile($pharFile = 'opengrok-cli.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'opengrok-cli.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->in(__DIR__.'/../../../vendor/symfony/console')
            ->in(__DIR__.'/../../../src')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../vendor/composer/autoload_real.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../vendor/composer/ClassLoader.php'));

        $content = file_get_contents(__DIR__ . '/../../../bin/opengrok-cli');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/opengrok-cli', $content);

        // Stubs
        $stub = '#!/usr/bin/env php
<?php

/**
 * (c) Danny Berger <dpb587@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Phar::mapPhar(\'opengrok-cli.phar\');

';

        if (getenv('OPENGROK_SERVER')) {
            $stub .= 'if (!getenv(\'OPENGROK_SERVER\')) putenv(' . var_export('OPENGROK_SERVER=' . getenv('OPENGROK_SERVER'), true) . ');' . "\n";
        }

        if (getenv('OPENGROK_PROJECT')) {
            $stub .= 'if (!getenv(\'OPENGROK_PROJECT\')) putenv(' . var_export('OPENGROK_PROJECT=' . getenv('OPENGROK_PROJECT'), true) . ');' . "\n";
        }

        $stub .= '
require \'phar://opengrok-cli.phar/bin/opengrok-cli\';

__HALT_COMPILER();';

        $phar->setStub($stub);

        $phar->stopBuffering();

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../../LICENSE'), false);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);

        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }

        $content = str_replace('@package_version@', $this->version, $content);

        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}
