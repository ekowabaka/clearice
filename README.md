ClearICE PHP Command Line Argument Parser
=========================================

A command line arguments parser for PHP. This tool helps CLI applications to 
parse their command line arguments. 

The argument style is similar to what you find with most GNU applications. Long 
options are preceeded with a double dash and short options are preceeded with a 
small dash. This means we can use `-s` for a short option and `--long-option` 
for a longer option. Options can have values assigned to them. A long option may 
take a value through an assignment like this `--long-option=value` and a short 
option may take a value through an assignment like this `-svalue`. A long option 
and a short option may be synonymous to each other so `-l` and `--long` may 
point to the same option.

This tool can also generate a help listing for your application. This means its
possible for your users can type `myapp --help` or `myapp -h` and get a help 
listing.

I worked on this tool before I discovered the PHP `getopt` function but I 
continued work on this tool mainly because it was well integrated with a couple
of CLI utilities I was working on. Also the automatic `--help` option makes
it somewhat of a GEM. 

Using the library
-----------------
This library has no dependencies so you just have to require or include the 
class's PHP file and you're good to go.

    require_once "ClearICE.php";

To add options for the parser use the `ClearICE::addOptions` method. This method
takes as many arguments as you want. Each argument is a structured array describing
an option your application understands.

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
    );
