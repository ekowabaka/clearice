ClearIce PHP Command Line Argument Parser
=========================================

[![Build Status](https://travis-ci.org/ekowabaka/clearice.png)](https://travis-ci.org/ekowabaka/clearice) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/quality-score.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Code Coverage](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/coverage.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Latest Stable Version](https://poser.pugx.org/ekowabaka/clearice/version.svg)](https://packagist.org/packages/ekowabaka/clearice)
[![Total Downloads](https://poser.pugx.org/ekowabaka/clearice/downloads)](https://packagist.org/packages/ekowabaka/clearice)

ClearIce helps PHP CLI applications to parse command line arguments which are 
presented in a style similar to what you would find in most GNU applications. 
It also allows you to perform simple I/O operations such as outputing text to standard
output (with the capability of controlling output verbosity levels) and reading input
from the standard input (with the capability of interactively validating input).
Another cool feature of ClearIce is that it can automatically generate help
texts for your apps.

Using ClearIce
--------------
If you manage your projects dependencies with composer then you can easily require
[ekowabaka/composer](http://packagist.org/packages/ekowabaka/clearice) to have
clearice included in your application. 

To use clearice to parse command line arguments you can simply put ...

````php
<?php
require "vendor/autoload.php";
$options = \clearice\ClearIce::parse();
print_r($options);
````

in a file (which you can for example save as wiki.php). Then executing ...

    php wiki.php generate --input=/home/james --output=/var/www/cool-wiki

would produce ...

    Array
    (
        [input] => /home/james
        [output] => /var/www/cool-wiki
        [stand_alones] => Array
            (
                [0] => generate
            )

        [unknowns] => Array
            (
                [0] => input
                [1] => output
            )

    )

Form more information on how to use clearice you can read through the 
documentation. Happy programming ...

Features
--------
A summary of what ClearIce can currently do.
- Command line argument parsing.
- Command line argument validation.
- Support for grouping valid arguments under specific commands.
- Automatic generation of help messages
- Automatic instantiation of Command classes for CLI applications (Framework?)
- Interactive console I/O with input validation.
- Any other stuff I forgot to mention.

Road Map
--------
What I hope to see in ClearIce some time in the future:

- Colour output on the terminal.
- Correction and suggestion of mis-spelled commands.
- Shell TAB completion of commands and options.
- Use readline for reading console inputs.
- A whole lot of awesomeness!

License
-------
Copyright (c) 2012-2014 James Ekow Abaka Ainooson

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

