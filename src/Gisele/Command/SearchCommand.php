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

namespace Gisele\Command;

use Symfony\Component\Console\Command\Command as BaseCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

use Goutte\Client;

abstract class SearchCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED)
            ->addOption(
                'throttle',
                't',
                InputOption::VALUE_REQUIRED,
                'The number of seconds between each requests',
                '5,10'
            )
            ->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Ask confirmation before fetching next page')
            ->addOption('max-result', 'm', InputOption::VALUE_REQUIRED, 'The maximum of result to fetch', null)
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Custom format to render the results')
            ->addOption('lucky', 'l', InputOption::VALUE_NONE, 'Are you lucky? Stop at the first result');
    }

    protected function getCrawler($url, $query)
    {
        $url = sprintf(
            $url,
            $query
        );

        return $this->getHttpClient()
            ->request('GET', $url);
    }

    protected function getHttpClient()
    {
        $client = new Client();
        $client
            ->setHeader(
            'User-Agent',
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:12.0) Gecko/20100101 Firefox/12.0 FirePHP/0.7.1"
        );

        return $client;
    }

    protected function validateThrottleOptions(InputInterface $input)
    {
        $throttle = $input->getOption('throttle');
        if (preg_match_all('/^(\d+),(\d+)$/', $throttle, $matches) && 3 === count($matches)) {
            $throttle = sprintf('return rand(%d, %d);', $matches[1][0], $matches[2][0]);
        } else if (preg_match('/^(\d+)$/', $throttle)) {
            $throttle = sprintf('return %d;', $throttle);
        } else {
            throw new \InvalidArgumentException('Invalid value provided for --throttle');
        }

        return $throttle;
    }

    protected function parseUrlString($url)
    {
        $url = parse_url($url);

        $queryParameters = array();
        foreach (explode('&', $url['query']) as $segment) {
            list($param, $value) = explode('=', $segment);
            $queryParameters[$param] = $value;
        }

        $url['query'] = $queryParameters;

        return $url;
    }

    protected function waitForNextPage($throttle, OutputInterface $output, $verbose = false)
    {
        // I know that's 3v|l
        $seconds = eval($throttle);
        if ($verbose) {
            $output->writeln('');
            while (--$seconds) {
                $output->write(sprintf("\r<info>Waiting %d seconds...</info>", $seconds));
                sleep(1);
            }
            $output->writeln('');
        } else {
            sleep($seconds);
        }
    }

    protected function askConfirmationToContinue(OutputInterface $output)
    {
        return $this->getHelper('dialog')
                   ->askConfirmation(
                       $output,
                       '<info>Continue</info> [<comment>y</comment>]? ',
                       true
                   );
    }
}
