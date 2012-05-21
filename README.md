PHP Coding Standard Fixer
=========================

`PHP_CodeSniffer` is a good tool to find coding standards problems in your
project but the identified problems need to be fixed by hand, and frankly,
this is quite boring on large projects! The goal of the PHP coding Standard
Fixer tool is to automate the fixing of *most* issues.

The tool knows how to fix issues for the coding standards defined in the
soon-to-be-available PSR-1 and PSR-2 documents.

Installation
------------

Download the
[`php-cs-fixer.phar`](https://github.com/fabpot/PHP-CS-Fixer/raw/master/php-cs-fixer.phar)
file and store it somewhere on your computer.

Usage
-----

The `fix` command tries to fix as much coding standards
problems as possible on a given file or directory:

    php php-cs-fixer.phar fix /path/to/dir
    php php-cs-fixer.phar fix /path/to/file

You can limit the fixers you want to use on your project by using the
`--level` option:

    php php-cs-fixer.phar fix /path/to/project --level=psr1
    php php-cs-fixer.phar fix /path/to/project --level=psr2
    php php-cs-fixer.phar fix /path/to/project --level=all

When the level option is not passed, all PSR2 fixers and some additional ones
are run.

You can also explicitely name the fixers you want to use (a list of fixer
names separated by a comma):

    php php-cs-fixer.phar fix /path/to/dir --fixers=linefeed,short_tag,indentation

The list of supported fixers:

 * short_tag       [all] PHP code must use the long <?php ?> tags or the
                   short-echo <?= ?> tags; it must not use the other tag
                   variations.

 * trailing_spaces [PSR-1] PHP code must use the long <?php ?> tags or the
                   short-echo <?= ?> tags; it must not use the other tag
                   variations.

 * unused_use      [PSR-1] Unused use statements must be removed.

 * return          [PSR-1] An empty line feed should precede a return
                   statement.

 * phpdoc_params   [PSR-1] All items of the @param phpdoc tags must be
                   aligned vertically.

 * linefeed        [PSR-1] All PHP files must use the Unix LF (linefeed)
                   line ending.

 * eof_ending      [PSR-1] A file must always ends with an empty line feed.

 * indentation     [PSR-1] Code must use 4 spaces for indenting, not tabs.

 * braces          [PSR-1] Opening braces for classes and methods must go on
                   the next line, and closing braces must go on the next
                   line after the body. Opening braces for control
                   structures must go on the same line, and closing braces
                   must go on the next line after the body.

 * elseif          [PSR-1] The keyword elseif should be used instead of else
                   if so that all control keywords looks like single words.

You can tweak the files and directories being analyzed by creating a
`.php_cs` file in the root directory of your project:

    <?php

    return Symfony\Component\Finder\Finder::create()
        ->name('*.php')
        ->exclude('someDir')
        ->in(__DIR__)
    ;

The `.php_cs` file must return a PHP iterator, like a Symfony
Finder instance.

You can also use specialized "finders", for instance when ran for Symfony
2.0 or 2.1:

    # For the Symfony 2.0 branch
    php php-cs-fixer.phar fix /path/to/sf20 Symfony21Finder

    # For the Symfony 2.1 branch
    php php-cs-fixer.phar fix /path/to/sf21 Symfony21Finder

Helpers
-------

If you are using Vim, install the dedicated
[plugin](https://github.com/stephpy/vim-php-cs-fixer).

Contribute
----------

The tool comes with quite a few built-in fixers and finders, but everyone is
more than welcome to contribute more of them.

### Fixers

A *fixer* is a class that tries to fix one CS issue (a `Fixer` class must
implement `FixerInterface`).

### Finders

A *finder* filters the files and directories scanned by the tool when run in
the directory of your project when the project follows a well-known directory
structures (like for Symfony projects for instance).
