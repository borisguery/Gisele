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

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

class WebSearchCommand extends SearchCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('web')
            ->setDescription('Web search')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $throttle = $this->validateThrottleOptions($input);
        $query = $input->getArgument('query');

        $format = (null !== $input->getOption('format')) ? $input->getOption('format') : "{counter}) {title} - {link}";

        $crawler = $this->getCrawler(
                'https://www.google.com/search?um=1&hl=en&q=%s&safe=off&tbs=isz:l',
                $query
        );

        $counter = 0;
        $maxResult = $input->getOption('max-result') ? (int) $input->getOption('max-result') : null;
        $resultLeft = true;

        while ($resultLeft) {
            foreach ($crawler->filter('#center_col h3.r a') as $element) {
                $parsedUrl = $this->parseUrlString($element->getAttribute('href'));

                $line = str_replace(
                    array(
                        '{counter}',
                        '{title}',
                        '{link}',
                    ),
                    array(
                        ++$counter,
                        $element->textContent,
                        urldecode($parsedUrl['query']['q']),
                    ),
                    $format
                );

                $output->writeln(sprintf('%s', $line));

                if (($maxResult && $counter >= $maxResult) || true === $input->getOption('lucky')) {
                    $resultLeft = false;
                    break;
                }
            }

            if (!$resultLeft) {
                break;
            }

            $link = $crawler->selectLink('Next');
            try {
                $link = $link->link();
                if ($input->getOption('interactive')) {
                    if (!$this->askConfirmationToContinue($output)) {
                        break;
                    }
                } else {
                    $this->waitForNextPage($throttle, $output, $input->getOption('verbose'));
                }
                $crawler = $this->getHttpClient()->click($link);
            } catch (\InvalidArgumentException $e) {
                $resultLeft = false;
                break;
            }
        }
    }
}
