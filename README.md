ClearICE PHP Command Line Argument Parser
=========================================

This tool helps PHP CLI applications to parse their command line arguments.It
provides an argument style is similar to what you find with most GNU applications. 

Long options are preceeded with a double dash `--` and short options are preceeded with a 
single dash `-`. This means we can use `-s` for a short option and `--long-option` 
for a longer option. Options can have values assigned to them. A long option may 
take a value through an assignment like this `--long-option=value` and a short 
option may take a value through an assignment like this `-svalue`. Long options 
and a short options may be synonymous to each other so `-l` and `--long` may 
point to the same option.

It is worth noting that a group of short options may also be specified preceeded 
by a single dash. For example if `a`, `b` and `c` are all valid options which
do not take values, passing `-abc` would be equivalent to passing `-a -b -c`.

Arguments that are not preceeded by either a single dash or a double dash would
be returned as stand alone arguments and the application may be required to deal
with them.

Another feature this tool has is the ability to generate a help listing for your 
application. This means its possible for your users can type `myapp --help` or 
`myapp -h` and get a help listing. Of course you'll have to provide some of the
content for that but the tool helps format and display it in a very presentable
form.

I worked on this tool before I discovered the PHP `getopt` function but I 
continued work on this tool mainly because it was well integrated with a couple
of CLI utilities I was working on. Also the automatic `--help` option makes
it somewhat of a GEM. 

Using the library
-----------------
This library has no dependencies so you just have to require or include the 
class's PHP file and you're good to go.

    require_once "ClearICE.php";

### Adding Options
To add options for the parser use the `ClearICE::addOptions` method. This method
takes as many arguments as you want. Each argument is a structured array describing
an option your application accepts.

    ClearICE::addOptions(
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
        )    
    );

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

### Parsing options
Option parsing can be performed by calling the `ClearICe::parse()` method. The
parse method returns an array which contains the options that were successfully
parsed. The array has the options as the keys and the values which were entered on
the command line interface as the array values. If an option has both short
and long keys then irrespective of whether a short or long key was passed 
on the CLI, the array key would be the long option. In cases where the options
do not take values, the value associated to the key array is the boolean value true.


For example the following script is intended for an app which
generates a wiki. 

    // Require the clear ice sources
    require_once "ClearICE.php";

    // Add options
    ClearICE::addOptions(
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

    $options = ClearICE::parse();
    print_r($options);

Assuming you've save this in a script `wiki.php` then when you pass any of the
following through the command line:

    php wiki.php --input=/myfiles/wiki-sources --output=/myfiles/wiki

or

    php wiki.php -i/myfiles/wiki-sources -o/myfiles/wiki

or

    php wiki.php -i/myfiles/wiki-sources --output=/myfiles/wiki

or

    php wiki.php --input=/myfiles/wiki-sources -o/myfiles/wiki

Your output would always be:

    Array
    (
        [input] => /myfiles/wiki-sources
        [output] => /myfiles/wiki
    )

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

    php test.php -i/myfiles/wiki-sources --output=/myfiles/wiki -v

You would get

    Array
    (
        [input] => /myfiles/wiki-sources
        [output] => /myfiles/wiki
        [verbose] => 1
    )
    
You can mix up the short options by using a single dash. So something like

    php test.php -v -x

or

    php test.php -vx

would all yield:

    Array
    (
        [verbose] => 1
        [create-default-index] => 1
    )

For short options which take options you can try

    php test.php -vxi/myfiles/wiki-sources

which would give you

    Array
    (
        [verbose] => 1
        [create-default-index] => 1
        [input] => /myfiles/wiki-sources
    )

### Auto Generating Help