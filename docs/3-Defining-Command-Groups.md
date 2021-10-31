---
title: Defining Command Groups
---
Defining Command Groups
=======================

Sometimes your CLI application may have different modes of operation, or it may just perform different related functions. In such cases, you might decide to keep a single interface with multiple command modes. Think about `git` and all its commands like `git init`  and `git commit` &mdash; and you should have an idea of what we're working towards.  

Different modes or commands may require their own set of options. ClearIce deals with this through its command groups feature. When command groups are defined, the argument parser determines the required command from the arguments and parses only options defined for the said command. While in command mode, options could be defined for given commands, or they could be defined to run for all or no commands.

Just as with options, commands that are to be parsed by ClearIce need to be explicitly specified. Once commands are specified, options which are specific to these commands also need to be specified. 

Supposing we wanted to extend our wiki script so it can act both as a wiki generator and a web server. We can use commands to tell the application which mode to run in. One way to implement this in our wiki would be to add `generate` and `serve` as two new commands. We can add the following code to our existing example:

````php
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

Once commands are defined, options can be assigned to these commands. Considering our running example, all options we have previously defined can be assigned to the generate command. We can do this by modifying our `addOption` calls and specifying the command for each option.

````php
$argumentParser->addOption([
    'command' => 'generate',
    'short_name' => 'o',
    'name' => 'output',
    'type' => 'string',
    'help' => 'specifies output directory of wiki',
    'value' => 'DIRECTORY',
    'required' => true
]);
$argumentParser->addOption([
    'command' => 'generate',
    'short_name' => 'i',
    'name' => 'input',
    'type' => 'string',
    'help' => 'specifies input sources for the wiki',
    'default' => '.',
]);
$argumentParser->addOption
````

Notice that we have added the `generate` command to the `input` and `output` option definitions. This means that these options would only be available when the generate command is executed. We can now go ahead to add a `port` option to the `serve` command.

````php
$argumentParser->addOption([
    'command' => 'serve',
    'short_name' => 'p',
    'name' => 'port',
    'type' => 'number',
    'value' => 'PORT',
    'help' => "specifies the port on which to run the server",
]);
````

Any options added to the parser without a `command` parameter can be parsed across all commands. In a way, these options can be considered as global to all commands, and they can additionally be used when there are no commands specified.

Sometimes there may be a particular option that will have to work with multiple commands. In such cases, you can pass an array with the names of all supported commands to the option. For example, with our hypothetical wiki app, if we want to add a location option to both the `generate` and `serve` commands, we can do the following:

````php
$argumentParser->addOption([
    'command' => ['serve', 'generate']
    'short_name' => 'L',
    'name' => 'location',
    'type' => 'string',
    'value' => 'LOCATION',
    'help' => "specifies the location path of the wiki",
]);
````





