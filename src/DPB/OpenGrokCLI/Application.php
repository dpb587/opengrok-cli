<?php

/**
 * (c) Danny Berger <dpb587@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DPB\OpenGrokCLI;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\HelpCommand;

class Application extends BaseApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return 'opengrok-cli';
    }

    protected function getDefaultCommands()
    {
        return array(
            new HelpCommand(),
            new RunCommand(),
        );
    }
}
