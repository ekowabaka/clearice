---
title: Generating Help Messages
---
Generating Help Messages
========================

[[_TOC_]]

Another feature ClearIce provides is the automatic generation of a help listing for your application. Through ClearIce, its possible for your users to type `myapp --help` or `myapp -h` (or even `myapp help` when commands are enabled) to get help about which options exist and what they mean. For CLI apps that use command groups, the help system can also provide help messages for each command defined in the application. Although you'll have to provide some of the content for the help magic to happen, the tool helps format and display your help consistently.

Enabling help generation
------------------------
You can automatically generate a help option by calling the `enableHelp()` method on the `ArgumentParser` instance. Provided you have supplied the help lines for your options through the `help` parameter, you'll be all set to go. Let's see an example with the following code.

````php
<?php

require "vendor/autoload.php";

use clearice\argparser\ArgumentParser;

$argumentParser = new ArgumentParser();
$argumentParser->addCommand(['name' => 'serve', 'help' => 'start a web server to serve the wiki']);
$argumentParser->addCommand(['name' => 'generate', 'help' => 'generate the output wiki']);

$argumentParser->addOption([
    'short_name' => 'o',
    'name' => 'output',
    'type' => 'string',
    'help' => 'specifies output directory of wiki',
    'value' => 'DIRECTORY',
    'required' => true,
    'command' => 'generate'
]);
$argumentParser->addOption([
    'short_name' => 'i',
    'name' => 'input',
    'type' => 'string',
    'help' => 'specifies input sources for the wiki',
    'default' => '.',
    'command' => 'generate'
]);
$argumentParser->addOption([
    'command' => 'serve',
    'short_name' => 'p',
    'name' => 'port',
    'type' => 'number',
    'value' => 'PORT',
    'help' => "specifies the port on which to run the server",
]);
$argumentParser->addOption([
    'name' => 'verbose',
    'short_name' => 'v',
    'help' => "display detailed output of commands.",
]);

$argumentParser->enableHelp();
$output = $argumentParser->parse();
````

With the example code above if you should execute ...

    php wiki.php --help

or  ...

    php wiki.php -h

you should get:


    Usage:
    wiki.php [COMMAND] [OPTIONS] ...

    Commands:
    serve               start a web server to serve the wiki
    generate            generate the output wiki
    help                display help for any command
                        Usage: wiki.php help [command]

    Options:
    -v, --verbose              display detailed output of commands.
    -h, --help                 display this help message

You can get help on any of the individual commands by executing:

    php wiki.php generate --help

or

    php wiki.php help generate

For our example, this will output.


    Usage:
    wiki.php [COMMAND] [OPTIONS] ...

    Commands:
    serve               start a web server to serve the wiki
    generate            generate the output wiki
    help                display help for any command
                        Usage: wiki.php help [command]

    Options:
    -v, --verbose              display detailed output of commands.
    -h, --help                 display this help message


Adding details to your help message
-----------------------------------
The `enableHelp` method takes three optional arguments that allow you to specify a description (header) and a footer for the help message. It also gives you the ability to override the name of the command, which is automatically detected by default, to whatever you want. Going back to our example, if you change the call to `enableHelp` to the following:

````php
$argumentParser->enableHelp(
    
    "Simple Wiki version 1.0\nA sample or should I say dummy wiki app "
    .   "to help explain ClearIce. This app practically does nothing.",

    "Hope you had a nice time learning about ClearIce. We're pretty sure "
    .   "your cli apps would no longer be boring to work with.\n\nReport bugs to "
    .   "bugs@clearice.tld"
);
````

Your help command (`php wiki.php -h` or `php wiki.php --help`) should now generate ...

    Simple Wiki version 1.0
    A sample or should I say dummy wiki app to help explain ClearIce. This app
    practically does nothing.

    Usage:
    wiki.php [COMMAND] [OPTIONS] ...

    Commands:
    serve               start a web server to serve the wiki
    generate            generate the output wiki
    help                display help for any command
                        Usage: wiki.php help [command]

    Options:
    -v, --verbose              display detailed output of commands.
    -h, --help                 display this help message

    Hope you had a nice time learning about ClearIce. We're pretty sure your
    cli apps would no longer be boring to work with.

    Report bugs to bugs@clearice.tld
