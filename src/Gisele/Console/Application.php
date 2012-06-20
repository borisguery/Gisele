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

namespace Gisele\Console;

use Symfony\Component\Console\Application as BaseApplication,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Output\ConsoleOutput,
    Symfony\Component\Console\Formatter\OutputFormatter,
    Symfony\Component\Console\Formatter\OutputFormatterStyle,
    Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Finder\Finder;

use Gisele\Gisele;

class Application extends BaseApplication
{

    public function __construct()
    {
        parent::__construct('Gisele', Gisele::VERSION);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $output) {
            $styles['highlight'] = new OutputFormatterStyle('red');
            $styles['warning'] = new OutputFormatterStyle('black', 'yellow');
            $formatter = new OutputFormatter(null, $styles);
            $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
        }

        $this->registerNativeCommands();

        return parent::run($input, $output);
    }

    protected function registerNativeCommands()
    {
        $this->addCommands(
            array(
                new \Gisele\Command\ImageSearchCommand(),
                new \Gisele\Command\WebSearchCommand(),
                new \Gisele\Command\NewsSearchCommand(),
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new DialogHelper());

        return $helperSet;
    }
}
