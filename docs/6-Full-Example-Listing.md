Full Example Listing
====================
Here's a full example listing that demonstrates the typical usage of clearice. Feel free to copy and modify for your own use.

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

$argumentParser->enableHelp(
    "Simple Wiki version 1.0\nA sample or should I say dummy wiki app "
    .   "to help explain ClearIce. This app practically does nothing.",
    "Hope you had a nice time learning about ClearIce. We're pretty sure "
    .   "your cli apps would no longer be boring to work with.\n\nReport bugs to "
    .   "bugs@clearice.tld"
);
$output = $argumentParser->parse();
print_r($output);

````
