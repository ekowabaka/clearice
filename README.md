ClearIce PHP Command Line Argument Parser
=========================================

[![Build Status](https://travis-ci.org/ekowabaka/clearice.png)](https://travis-ci.org/ekowabaka/clearice) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/quality-score.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Code Coverage](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/coverage.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Latest Stable Version](https://poser.pugx.org/ekowabaka/clearice/version.svg)](https://packagist.org/packages/ekowabaka/clearice)
[![Total Downloads](https://poser.pugx.org/ekowabaka/clearice/downloads)](https://packagist.org/packages/ekowabaka/clearice)

ClearIce helps PHP CLI applications with the  parsing of command line arguments. Arguements supplied for parsing must be presented in a style similar to what you would find in most GNU applications. Apart from parsing command line input, ClearIce allows you to also perform simple I/O operations such as: outputing text to standard output or standard error (with the added capability of filtering output based on verbosity levels), as well as reading from the standard input (also with the added capability of interactively validating input). Finally, ClearIce can automatically generate help messages for your apps.

Using ClearIce
--------------
If you manage your projects dependencies with composer then you can easily require
[ekowabaka/clearice](http://packagist.org/packages/ekowabaka/clearice) to have
clearice included in your application. 

To use clearice to parse command line arguments you can put ...

````php
<?php
require "vendor/autoload.php";

$parser = new \clearice\argparser\ArgumentParser();
$parser->addOption([
    'name' => 'input',
    'short_name' => 'i',
    'type' => 'string',
    'required' => true
]);

$parser->addOption([
    'name' => 'output',
    'short_name' => 'o',
    'type' => 'string',
    'default' => '/default/output/path'
]);

$options = $parser->parse($argv);
print_r($options);
````

... in a file (which you can for example save as wiki.php). Then executing ...

    php wiki.php generate --input=/home/james --output=/var/www/cool-wiki

... would produce ...

    Array
    (
        [input] => /input/path
        [output] => /output/path
    )

... and so will the following:

    php test.php --input /input/path --output /output/path
    php test.php -i/input/path -o/output/path

Form more information on how to use clearice you can read through the 
documentation. Happy programming ...

License
-------
Copyright (c) 2012-2018 James Ekow Abaka Ainooson

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

