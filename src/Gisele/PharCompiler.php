<?php
/**
 * Gisele
 *
 * LICENSE
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 *
 * @author      Boris Guéry <guery.b@gmail.com>
 * @license     http://sam.zoy.org/wtfpl/COPYING
 * @link        https://github.com/borisguery/gisele
 */

namespace Gisele;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Process\Process;

class PharCompiler {

    protected $version;

    public function compile($pharFile = 'gisele.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $p = new Process('git rev-parse HEAD', __DIR__);
        if (0 !== $p->run()) {
            throw new \RuntimeException('Unable to get current version, you must compile the phar from the Gisele git repository');
        }

        $this->version = trim($p->getOutput());

        $phar = new \Phar($pharFile, 0, 'gisele.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $finder = Finder::create()->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('PharCompiler.php')
            ->in(__DIR__.'/../')
        ;

        // Add src
        foreach ($finder as $file) {
            $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            if (false !== strpos($path, 'Gisele.php')) {
                $content = str_replace('@version@', substr($this->version, 0, 8), file_get_contents($path));
                $phar->addFromString($path, $content);
            } else {
                $phar->addFile($path);
            }
        }

        $finder = Finder::create()->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->exclude('bin')
            ->in(__DIR__.'/../../vendor/fabpot/')
            ->in(__DIR__.'/../../vendor/guzzle/')
            ->in(__DIR__.'/../../vendor/symfony/')
            ->in(__DIR__.'/../../vendor/composer/')
        ;

        foreach ($finder as $file) {
            $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $phar->addFile($path);
        }

        $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', realpath(__DIR__.'/../../vendor/autoload.php'));
        $phar->addFile($path);

        $content = file_get_contents(__DIR__.'/../../bin/gisele');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/gisele', $content);

        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        unset($phar);
    }

    private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php
/**
 * Gisele
 *
 * LICENSE
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 *
 * @author      Boris Guéry <guery.b@gmail.com>
 * @license     http://sam.zoy.org/wtfpl/COPYING
 * @link        https://github.com/borisguery/gisele
 *
 * This file is part of Gisele.
 */

Phar::mapPhar('gisele.phar');

EOF;

        return $stub .= <<<'EOF'
require 'phar://gisele.phar/bin/gisele';

__HALT_COMPILER();
EOF;
    }
}
