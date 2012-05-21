<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\CS\Fixer;
use Symfony\CS\FixerInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FixCommand extends Command
{
    protected $fixer;

    public function __construct()
    {
        $this->fixer = new Fixer();
        $this->fixer->registerBuiltInFixers();

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fix')
            ->setDefinition(array(
                new InputArgument('path', InputArgument::REQUIRED, 'The path'),
                new InputArgument('finder', InputArgument::OPTIONAL, 'The Finder short class name to use', 'SymfonyFinder'),
                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified'),
                new InputOption('level', '', InputOption::VALUE_REQUIRED, 'The level of fixes (can be psr1, psr2, or all)', 'all'),
                new InputOption('fixers', '', InputOption::VALUE_REQUIRED, 'A list of fixers to run'),
            ))
            ->setDescription('Fixes a directory or a file')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command tries to fix as much coding standards
problems as possible on a given file or directory:

    <info>php %command.full_name% /path/to/dir</info>
    <info>php %command.full_name% /path/to/file</info>

You can limit the fixers you want to use on your project by using the
<comment>--level</comment> option:

    <info>php %command.full_name% /path/to/project --level=psr1</info>
    <info>php %command.full_name% /path/to/project --level=psr2</info>
    <info>php %command.full_name% /path/to/project --level=all</info>

When the level option is not passed, all PSR2 fixers and some additional ones
are run.

You can also explicitely name the fixers you want to use (a list of fixer
names separated by a comma):

    <info>php %command.full_name% /path/to/dir --fixers=linefeed,short_tag,indentation</info>

The list of supported fixers:

{$this->getFixersHelp()}
You can tweak the files and directories being analyzed by creating a
<comment>.php_cs</comment> file in the root directory of your project:

    <?php

    return Symfony\Component\Finder\Finder::create()
        ->name('*.php')
        ->exclude('someDir')
        ->in(__DIR__)
    ;

The <comment>.php_cs</comment> file must return a PHP iterator, like a Symfony
Finder instance.

You can also use specialized "finders", for instance when ran for Symfony
2.0 or 2.1:

    <comment># For the Symfony 2.0 branch</comment>
    <info>php %command.full_name% /path/to/sf20 Symfony21Finder</info>

    <comment># For the Symfony 2.1 branch</comment>
    <info>php %command.full_name% /path/to/sf21 Symfony21Finder</info>
EOF
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $filesystem = new Filesystem();
        if (!$filesystem->isAbsolutePath($path)) {
            $path = getcwd().DIRECTORY_SEPARATOR.$path;
        }

        if (is_file($path)) {
            $iterator = new \ArrayIterator(array(new \SplFileInfo($path)));
        } elseif (file_exists($config = $path.'/.php_cs')) {
            $iterator = include $config;
        } else {
            $class = 'Symfony\\CS\\Finder\\'.$input->getArgument('finder');
            $iterator = new $class($path);
        }

        if ($input->getOption('fixers')) {
            $fixerConfig = array_map('trim', explode(',', $input->getOption('fixers')));
        } else {
            switch ($input->getOption('level')) {
                case 'psr1':
                    $fixerConfig = FixerInterface::PSR1_LEVEL;
                    break;
                case 'psr2':
                    $fixerConfig = FixerInterface::PSR2_LEVEL;
                    break;
                case 'all':
                    $fixerConfig = FixerInterface::ALL_LEVEL;
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('The level "%s" is not defined.', $input->getOption('level')));
            }
        }

        $changed = $this->fixer->fix($iterator, $fixerConfig, $input->getOption('dry-run'));

        foreach ($changed as $i => $file) {
            $output->writeln(sprintf('%4d) %s', $i, $file));
        }
    }

    protected function getFixersHelp()
    {
        $fixers = '';
        $maxName = 0;
        foreach ($this->fixer->getFixers() as $fixer) {
            if (strlen($fixer->getName()) > $maxName) {
                $maxName = strlen($fixer->getName());
            }
        }

        $count = count($this->fixer->getFixers()) - 1;
        foreach ($this->fixer->getFixers() as $i => $fixer) {
            $chunks = explode("\n", wordwrap(sprintf('[%s] %s', $this->fixer->getLevelAsString($fixer), $fixer->getDescription()), 72 - $maxName, "\n"));
            $fixers .= sprintf(" * <comment>%s</comment>%s %s\n", $fixer->getName(), str_repeat(' ', $maxName - strlen($fixer->getName())), array_shift($chunks));
            while ($c = array_shift($chunks)) {
                $fixers .= str_repeat(' ', $maxName + 4).$c."\n";
            }

            if ($count != $i) {
                $fixers .= "\n";
            }
        }

        return $fixers;
    }
}
