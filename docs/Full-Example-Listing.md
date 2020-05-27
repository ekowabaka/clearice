Full Example Listing
--------------------

````php
<?php
require_once "vendor/autoload.php";

use clearice\ClearIce;

$cli = new ClearIce();

// Add commands
ClearIce::addCommands(
    array(
        'command' => 'generate',
        'help' => 'generate a wiki'
    ),
    array(
        'command' => 'serve',
        'help' => 'start an HTTP server to serve a wiki'
    )
);

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

ClearIce::addOptions(
    array(
        'short' => 'p',
        'long' => 'port',
        'has_value' => true,
        'help' => "specifies the port on which to run the server",
        'command' => 'serve'
    )
);

ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
ClearIce::setUsage("[command] [options]..");
ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");

ClearIce::setStrict(true);
ClearIce::addHelp();
$options = ClearIce::parse();
print_r($options);
````
