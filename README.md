ClearIce PHP Command Line Argument Parser
=========================================

[![Build Status](https://travis-ci.org/ekowabaka/clearice.png)](https://travis-ci.org/ekowabaka/clearice) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/quality-score.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Code Coverage](https://scrutinizer-ci.com/g/ekowabaka/clearice/badges/coverage.png)](https://scrutinizer-ci.com/g/ekowabaka/clearice/)
[![Latest Stable Version](https://poser.pugx.org/ekowabaka/clearice/version.svg)](https://packagist.org/packages/ekowabaka/clearice)
[![Total Downloads](https://poser.pugx.org/ekowabaka/clearice/downloads)](https://packagist.org/packages/ekowabaka/clearice)

ClearIce provides tools that allow PHP applications to parse command line arguments, and perform interactive I/O sessions. Arguments supplied at the command line, or through a shell are validated and supplied to your script in an organized format, with the added possibility of automatically generating help messages for your command line applications. 

Installing 
----------
ClearIce is best installed through composer.
    
    composer require ekowabaka/clearice
    
If for some reason you don't want to use composer, you can simply include all the needed clearice scripts where you need them. ClearIce has no dependencies other than a PHP interpreter with version 7.1 or better.

Parsing Arguments with ClearICE
--------------
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
        [__executed] => wiki.php
    )

... and so will the following:

    php test.php --input /input/path --output /output/path
    php test.php -i/input/path -o/output/path

Interactive I/O with ClearICE
--------------

And for an example of interactive I/O, entering this 

````php
use clearice\io\Io;
$io = new Io();
$name = $io->getResponse('What is your name', ['default' => 'No Name']);

$direction = $io->getResponse("Okay $name, where do you want to go", 
    [
        'required' => true,
        'answers' => array('north', 'south', 'east', 'west')
    ]
); 
````

could lead to an interaction like this:

    What is your name [No Name]: ⏎
    Okay No Name, where do you want to go (north/south/east/west) []: ⏎
    A value is required.
    Okay No Name, where do you want to go (north/south/east/west) []: home⏎
    Please provide a valid answer.
    Okay No Name, where do you want to go (north/south/east/west) []: 


