[[_TOC_]]

Parsing Command Line Arguments
==============================
ClearIce is a command line argument parser. It allows you to define which options and commands are passed to your application, and parses them for you, complete with error reporting. Option styles accepted through ClearIce are quite similar to what you would expect from most GNU style applications. Arguments passed to your application could be options that your users can set through flags, command names that your users may pass to select a specic mode of  your application, or stand-alone arguments that serve other purposes in your application. This section provides a complete walkthrough of all the features of ClearIce's argument parser.

Defining Options for your Application
-------------
When written out on the command line, long options are preceeded with a double dash `--`, and short options are preceeded with a single dash `-`. This means we can use `-s` for a short option, and `--long-option` for a longer option. Options can have values assigned to them. A long option may take a value through an assignment like this `--long-option=value`, or this `--long-option value`, provided the specific option is configured to take values. A short option configured to take values, on the other hand, may take a value through an assignment like this `-svalue`, or this `-s value`.  Long options and a short options may be synonymous to each other &mdash; `-l` and `--long` can be made to resolve to the same option.

ClearIce presents a convenience feature, where a group of short options can be specified preceeded by a single dash. For example if `a`, `b` and `c` are all valid options that do not take values, passing `-abc` would be equivalent to passing `-a -b -c`.

Arguments that are not preceeded by either a single dash or a double dash would be returned as stand alone arguments provided they do not follow options which accept values. In cases where stand alone arguments are not absorbed as values, ClearIce would return such arguments and the application would be required to deal with them.

Command Groups
--------------
Sometimes your CLI application may have different modes of operation, or may just perform different related functions. In such cases, different modes or commands may require their own set of options. ClearIce deals with this requirement through command groups. When command groups are defined, the argument parser determines the required command and parses only arguments defined for the said command.

Automated Help
--------------
Another feature ClearIce provides is the automatic generation of a help listing for your application. This means its possible for your users can type `myapp --help` or 
`myapp -h` and get a help listing. For cli apps which use command groups, the help system can also provide help messages for each command defined in the application. Although you'll have to provide some of the content for all the help magic to happen, the tool helps format and display your content in a very consistent form.

Adding Options
--------------
To add options for the parser use the `ClearIce::addOptions` method. This method
takes as many arguments as you want with each argument representing an option you
want to make parsable. An argument could either be a single string for a very
simple option or a structured array for a much more elaborate option. 

````php
use clearice\ClearIce;

ClearIce::addOptions(
    'input',
    array(
        'short' => 'o',
        'long' => 'output',
        'has_value' => true,
        "help" => "specifies where the wiki should be written to"
    ),
    array(
        'short' => 'v',
        'long' => 'verbose',
        "help" => "displays detailed information about everything that happens"
    )    
);
````

The structured array has the following keys:

* `short` : this specifies the character to use for the short option. Note that
  short options are case sensitive.

* `long` : this specifies the text to use for the long option. Note that the long
  option cannot have spaces or wild card characters in them. The parser only 
  recognizes the characters 0-9, a-z and A-Z. It also recognizes the hyphen 
  character `-` and the period character `.`.

* `has_value` : is either true or false to specify whether the option takes a 
  value or not. This option is very critical for short options which take values.
  Once a short option is specified as having a value, all other characters which
  follow the option character are parsed as the option's value even if they may
  represent valid options.

* `help` : is a line of text that is rendered as part of the help text in the
  automatically generated help text.

* `multi` : is a boolean to specify whether this option can take multiple values.
   When `multi` is true every option which is passed multiple times on the
   command line would be returned by ClearIce in an array. If multi is false
   however, the value of the last option passed would be returned.

Parsing options
---------------
Option parsing can be performed by calling the `ClearICe::parse()` method. The
parse method returns an array which contains the options that were successfully
parsed. The array has the options as the keys and the values which were entered on
the command line interface as the array values. If an option has both short
and long keys then irrespective of whether a short or long key was passed 
on the CLI, the array key would be the long option. In cases where the options
do not take values, the value associated to the key array is the boolean value true.


For example the following script is intended for parsing the options of an app which
generates a wiki. 

````php
require "vendor/autoload.php";

use clearice\ClearIce;

// Add options
ClearIce::addOptions(
    array(
        'short' => 'i',
        'long' => 'input',
        'has_value' => true,
        'help' => "specifies where the input files for the wiki are found."
    ),
    array(
        'short' => 'o',
        'long' => 'output',
        'has_value' => true,
        "help" => "specifies where the wiki should be written to"
    ),
    array(
        'short' => 'v',
        'long' => 'verbose',
        "help" => "displays detailed information about everything that happens"
    ),
    array(
        'short' => 'x',
        'long' => 'create-default-index',
        "help" => "creates a default index page which lists all the wiki pages in a sorted order"
    )
);

$options = ClearIce::parse();
print_r($options);
````

Assuming you've save this in a script `wiki.php` then when you pass any of the
following through the command line:

    php wiki.php --input=/myfiles/wiki-sources --output=/myfiles/wiki

or

    php wiki.php -i/myfiles/wiki-sources -o/myfiles/wiki

or

    php wiki.php -i/myfiles/wiki-sources --output=/myfiles/wiki

or

    php wiki.php --input=/myfiles/wiki-sources -o/myfiles/wiki

or

    php wiki.php --input /myfiles/wiki-sources --output /myfiles/wiki

or

    php wiki.php --input /myfiles/wiki-sources -o /myfiles/wiki

or

    php wiki.php --input=/myfiles/wiki-sources -o /myfiles/wiki

Your output would always be:

    Array
    (
        [input] => /myfiles/wiki-sources
        [output] => /myfiles/wiki
    )

And if you take some time to look at the examples given you should realize that
there could be several more combinations which would end in the same output.

For options which do not take values like the `--verbose` option in our example

    php wiki.php --verbose

or

    php wiki.php -v

Your output would always be like

    Array
    (
        [verbose] => 1
    )

If you decide to mix them up like this

    php wiki.php -i/myfiles/wiki-sources --output=/myfiles/wiki -v

You would get

    Array
    (
        [input] => /myfiles/wiki-sources
        [output] => /myfiles/wiki
        [verbose] => 1
    )
    
You can mix up the short options by using a single dash. So something like

    php wiki.php -v -x

or

    php wiki.php -vx

would both equally yield:

    Array
    (
        [verbose] => 1
        [create-default-index] => 1
    )

For short options which take options you can try

    php wiki.php -vxi/myfiles/wiki-sources

which would give you

    Array
    (
        [verbose] => 1
        [create-default-index] => 1
        [input] => /myfiles/wiki-sources
    )

Parsing Commands
----------------
ClearIce supports the parsing of commands which are passed as command line arguments.
Just as with options, commands that are to be parsed by ClearIce need to be
explicitly specified. Once commands are specified, options which are specific
to given commands also need to specified. Furthermore, a command can specify a class
(which implements the `\clearice\Command` interface) to be instantiated and
executed whenever that command is run.

Supposing we wanted to extend our wiki script so it can act both as a wiki 
generator and a web server. We can use commands to tell the application which
mode to run in. The most logical way to implement this in our wiki would be to
add `generate` and `serve` as two new commands. We can add the following code 
to our existing example:

````php

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

````

Once this is done executing ...

    php wiki.php generate

would give us

    Array
    (
        [__command__] => generate
    )

The next logical thing to after this would be to assign the options to the
commands. So far our example has only been based on generating wikis and as such
most of the options we have defined can easily be assigned to the generate command.
We can do this by modifying our getOptions command as follows.

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

Stand Alone Arguments
---------------------
Arguments that neither fit the format for options nor are commands would be returned as stand alone
arguments. Assuming we want our little wiki script to take its input files and
directories through the command directly we can execute:

    php wiki.php generate /myfiles/wiki-sources -o/myfiles/wiki

This would then give us

    Array
    (
        [__command__] => generate
        [output] => /myfiles/wiki
        [stand_alones] => Array
            (
                [0] => /myfiles/wiki-sources
            )

    )

and 

    php wiki.php generate /myfiles/wiki-sources/Home.wiki /myfiles/wiki-sources/About.wiki -o/myfiles/wiki

would give:

    Array
    (
        [__command__] => generate
        [output] => /myfiles/wiki
        [stand_alones] => Array
            (
                [0] => /myfiles/wiki-sources/Home.wiki
                [1] => /myfiles/wiki-sources/About.wiki
            )

    )


Realise that a new key `stand_alones` was added. This key points to an array
of all the arguments which do not fit the format for arguments that are parsed
by ClearIce. 


Dealing with Unknown Options 
----------------------------
Some options may be parsed but they may not be understood. In such instances the
the class returns an `unknowns` key. Unknown options are still added to the output. 

This means if you should pass

    php wiki.php generate /myfiles/wiki-sources -o/myfiles/wiki -ug --unknown-option

you should get

    Array
    (
        [__command__] => generate
        [output] => /myfiles/wiki
        [unknowns] => Array
            (
                [0] => unknown-option
                [1] => u
                [2] => g
            )

        [unknown-option] => 1,
        [u] => 1,
        [g] => 2,
        [stand_alones] => Array
            (
                [0] => /myfiles/wiki-sources
            )

    )

You can use the parser in strict mode. This can be achieved by calling
`ClearIce::setStrict(true)` anywhere before the `ClearIce::parse()` method. The
parser would terminate the application with a friendly message if it should 
encounter any unknown options. 

In a strict mode the following:

    php wiki.php generate /myfiles/wiki-sources -o/myfiles/wiki -ug --unknown-option

would give the following friendly output:

    wiki.php: invalid option -- u
    wiki.php: invalid option -- g
    wiki.php: invalid option -- unknown-option



Auto Generating Help
--------------------
You can automatically generate a help option by calling the `ClearIce::addHelp()`
method. Once you have  provided help lines for all your options you pretty much 
have all you need.

With our example as shown above, if you should add `ClearIce::addHelp()` before
the `ClearIce::parse()` and execute:

    php wiki.php --help

or 

    php wiki.php -h

you should get:

    Commands:
    generate            generate a wiki
    serve               start an HTTP server to serve a wiki
    help                displays specific help for any of the given commands.
                        Usage: wiki.php help [command]

    Options:
      -v, --verbose              displays detailed information about everything
                                 that happens
      -h, --help                 shows this help message



You can add a description, usage information and footnotes by calling; 
`ClearIce::setDescription`, `ClearIce::setUsage` and `ClearIce::setFootnote`
respectively.

````php
ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
ClearIce::setUsage("[command] [options]..");
ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
````

Now your help command (`php wiki.php -h` or `php wiki.php --help`) would generate 

    Simple Wiki version 1.0
    A sample or should I say dummy wiki app to help explain ClearIce. This app
    practically does nothing.

    Usage:
      wiki.php [command] [options]..

    Commands:
    generate            generate a wiki
    serve               start an HTTP server to serve a wiki
    help                displays specific help for any of the given commands.
                        Usage: wiki.php help [command]

    Options:
      -v, --verbose              displays detailed information about everything
                                 that happens
      -h, --help                 shows this help message

    Hope you had a nice time learning about ClearIce. We're pretty sure your
    cli apps would no longer be boring to work with.

    Report bugs to bugs@clearice.tld


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
