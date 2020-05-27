Defining Command Groups
=======================
Sometimes your CLI application may have different modes of operation, or it may just perform different related functions. In such cases, you might decide to keep a single interface with multiple command modes. Think about `git` and all its commands like `git init`  and `git commit`, and you should have an idea of what we're working towards.  

Different modes or commands may require their own set of options. ClearIce deals with this through its command groups feature. When command groups are defined, the argument parser determines the required command from the arguments and parses only options defined for the said command. While in command mode, options could be defined for given commands, or they could be defined to run for all or no commands.

Just as with options, commands that are to be parsed by ClearIce need to be explicitly specified. Once commands are specified, options which are specific to these commands also need to be specified. 

Supposing we wanted to extend our wiki script so it can act both as a wiki generator and a web server. We can use commands to tell the application which mode to run in. One way to implement this in our wiki would be to add `generate` and `serve` as two new commands. We can add the following code to our existing example:

````php
<?php

require "vendor/autoload.php";

use clearice\argparser\ArgumentParser;

$argumentParser = new ArgumentParser();

$argumentParser->addCommand([
    'name' => 'serve', 
    'help' => 'start a web server to serve the wiki'
    ]);
$argumentParser->addCommand([
    'name' => 'generate', 
    'help' => 'generate the output wiki'
    ]);

$argumentParser->parse();

````

Once this is done, executing ...

    php wiki.php generate

would produce the following in the output.

    Array
    (
        [__command] => generate
        [__executed] => commands.php
    )

Once commands are defined, options can be assigned. So far, our example has been based on generating wikis. As such most of the options we have previously defined can be assigned to the generate command. We can do this by modifying our `addOption` calls by specifying the command for each option.

````php
// Add options
ClearIce::addOptions(
    array(
        'short' => 'i',
        'long' => 'input',
        'has_value' => true,
        'help' => "specifies where the input files for the wiki are found.",
        'command' => 'generate'
    ),
    array(
        'short' => 'o',
        'long' => 'output',
        'has_value' => true,
        "help" => "specifies where the wiki should be written to",
        'command' => 'generate'
    ),
    array(
        'short' => 'v',
        'long' => 'verbose',
        "help" => "displays detailed information about everything that happens"
    ),
    array(
        'short' => 'x',
        'long' => 'create-default-index',
        "help" => "creates a default index page which lists all the wiki pages in a sorted order",
        'command' => 'generate'
    )
);
````

Note that we have added the `generate` command to the `input`, `output` and 
`create-default-index` option specifications. This means that these options
would only be available when the generate command is executed. We can now go 
ahead to add a port option to the `serve` command.

````php
ClearIce::addOptions(
    array(
        'short' => 'p',
        'long' => 'port',
        'has_value' => true,
        'help' => "specifies the port on which to run the server",
        'command' => 'serve'
    )
);
````






