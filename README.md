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
