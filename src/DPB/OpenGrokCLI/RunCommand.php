<?php

/**
 * (c) Danny Berger <dpb587@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPB\OpenGrokCLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('opengrok-cli')
            ->setDefinition(
                array(
                    new InputArgument('query', InputArgument::REQUIRED, 'Search query'),
                    new InputOption('server', null, InputOption::VALUE_REQUIRED, 'The OpenGrok service address', getenv('OPENGROK_SERVER')),
                    new InputOption('project', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Search project(s)', explode(',', getenv('OPENGROK_PROJECT'))),
                    new InputOption('list', 'l', InputOption::VALUE_NONE, 'Output distinct file list'),
                    new InputOption('max-count', 'm', InputOption::VALUE_REQUIRED, 'The maximum number of files shown', 1000),
                    new InputOption('no-lines', null, InputOption::VALUE_NONE, 'Do not output line numbers after the file names'),
                    new InputOption('null', null, InputOption::VALUE_NONE, 'Output a zero byte (the ASCII NUL character) instead of the character that normally follows a file name.'),
                )
            )
            ->setDescription('Command line tool for OpenGrok searches.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getOption('project');
        $multiple = 1 < count($project);
        $color = $output->isDecorated();

        $optList = $input->getOption('list');
        $optNull = $input->getOption('null');
        $optNoLine = $input->getOption('no-lines');

        $dom = new \DOMDocument();
        $dom->loadHTML(
            file_get_contents(
                rtrim($input->getOption('server'), '/')
                    . '/search?'
                    . '&n=' . $input->getOption('max-count')
                    . '&q=' . rawurlencode($input->getArgument('query'))
                    . '&project=' . implode('&project=', $project)
                    . '&sort=fullpath'
            )
        );

        $xpath = new \DOMXPath($dom);
        $results = $xpath->query('//div[@id = "results"]/table/tr/td/tt[@class = "con"]/a[@class = "s"]');

        $last = null;

        for ($i = 0; $i < $results->length; $i ++) {
            $result = $results->item($i);

            preg_match('@^.*/xref/([^/]+)(/.*)#(\d+)$@', $result->getAttribute('href'), $file);

            $out = '';

            if ($color) {
                $out = (($multiple) ? "\033[33m{$file[1]}\033[36m:" : '') . "\033[35m{$file[2]}\033[0m";
            } else {
                $out = (($multiple) ? "{$file[1]}:" : '') . $file[2];
            }

            if ($optList) {
                if ($last == $file[1] . ':' . $file[2]) {
                    continue;
                }

                $last = $file[1] . ':' . $file[2];

                $out .= $null ? chr(0) : "\n";
            } else {
                if ($optNoLine) {
                    $out .= ($color ? "\033[36m:\033[0m" : ":");
                } else {
                    $out .= ($color ? "\033[36m:\033[32m{$file[3]}\033[36m:\033[0m" : ":{$file[3]}:");
                }

                $match = $dom->saveXML($result);

                if ($color) {
                    $match = preg_replace_callback(
                        '@<b>([^<]+)</b>@',
                        function ($match) {
                            return "\033[31m{$match[1]}\033[0m";
                        },
                        $match
                    );
                }

                $match = preg_replace('@^<span class="l">\d+</span>(.*)$@', '$1', html_entity_decode(strip_tags($match, '<span>')));

                $out .= $match . "\n";
            }

            $output->write($out, false, OutputInterface::OUTPUT_RAW);
        }

        if (0 == $results->length) {
            return 1;
        } elseif (0 < $xpath->query('//div[@id = "results"]/p[@class = "slider"]/a[@class = "more"]')->length) {
            fwrite(STDERR, 'Results truncated.' . "\n");
        }
    }
}
