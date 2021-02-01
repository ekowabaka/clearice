
Parsing Command Line Arguments
==============================

[[_TOC_]]

ClearIce is a command line argument parser for PHP applications. It allows you to define the options and commands that are passed to your application, and it parses them for you, complete with error reporting and help generation. Option styles accepted through ClearIce are quite similar to what you would expect from most GNU style applications. Arguments passed to your application could be commands that your users may use to select specific modes in your application, options that your users can pass through flags, or stand-alone arguments that serve other purposes (such as providing file paths) in your application. 

The ArgumentParser class
-------------------------
You can access the argument parser through the `clearice\argparser\ArgumentParser` class.

````php
<?php

$argumentParser = new \clearice\argparser\ArgumentParser();

````

Adding and Parsing options to the ArgumentParser
------------------------------------
There is an `addOption` method in the `ArgumentParser` class, which allows you to define an option that could be parsed from the command line arguments. Your option's definition must be passed as a single structured array to the `addOption` method. 

````php
use clearice\argparser\ArgumentParser;

$argumentParser = new ArgumentParser();
$argumentParser->addOption([
    'short_name' => 'o',
    'name' => 'output',
    'type' => 'string',
    'help' => 'specifies output directory of wiki',
    'value' => 'DIRECTORY',
    'required' => true
]);
$argumentParser->addOption([
    'short_name' => 'i',
    'name' => 'input',
    'type' => 'string',
    'help' => 'specifies input sources for the wiki',
    'default' => '.'
]);
````

The structured array for defining the option can have the keys described in the following table.

|Key            | Description |
|----           |-------------|
|`name`         | This specifies the name of the option. It is also used is also the flag that should be passed so this option can be activated. As such, the name cannot contain spaces or wild card characters. The parser only recognizes the characters 0-9, a-z and A-Z. It also recognizes the hyphen character `-` and the period character `.`.|
|`short_name`   | You can use this to add a short form of an existing option, or it could be an option standing on its own. It acts as a short option only when the `name` key is set. Essentially, either this value or the `name` must be set for the option description to be valid.|
|`type`         | This option can be set to either `string`, `number` or `flag`. When set as `string` or `number`, the option is validated to contain a string or a number respectively. When set as a `flag`, however, the option doesn't take any values, and acts as a boolean flag, which is set whenever the option is part of arguments passed. By default any option acts as a `flag` unless the type is specified.|
|`help`         | Through this option, you can provide a line of text that is rendered as part of the help text for options. |
|`repeats`      | This value is set to a boolean that specifies whether the option can be passed multiple times. When `repeats` is true and an option is passed multiple times, it's parsed output will be an array. |
|`default`      | The default value of the option when none is supplied. |
|`value`        | When displaying help for options, this value is used as an example value for options that take values. In case this is not supplied, a default value of `VALUE` is used instead|
|`required`     | Ensures that this option is always passed as part of the arguments. An error message is displayed, and the application is terminet when a required option is ommited. Whenever the required option is attached to a command group, the enforcement of the option takes place only when the command is activated. In the cases where an option has a default value, this key's value becomes unecessary.|

Whenever an array with invalid values is passed to the `addOption` method, an exception, `clearice\argparser\InvalidArgumentDescriptionException` is thrown.

Parsing options
---------------
Options can be parsed with the  `parse()` method in the `ArgumentParser` class. This method returns an array that contains the names of all options that were parsed, and their corresponding values. Because of how some options may be defined, they may have values returned after parsing regardless of whether they were written out as arguments when the application was started or not. Options that have default values and options that are defined as flags, fall in this category. The default values of options with values will have their defaults returned when they are not in the arguments, and options that are flags, will be presented with `false` values. The snippet below extends the running example with a call the parse method.

The following snippet extends our running example with more arguments and 

````php
use clearice\argparser\ArgumentParser;

$argumentParser = new ArgumentParser();
$argumentParser->addOption([
    'short_name' => 'o',
    'name' => 'output',
    'type' => 'string',
    'help' => 'specifies output directory of wiki',
    'value' => 'DIRECTORY',
    'required' => true
]);
$argumentParser->addOption([
    'short_name' => 'i',
    'name' => 'input',
    'type' => 'string',
    'help' => 'specifies input sources for the wiki',
    'default' => '.'
]);

$options = $argumentParser->parse();
````

When written out on the command line, the name for an option must be preceeded by a double dash `--`, and short options must be preceeded with a single dash `-`. This means we can use `-s` for a short option, and `--long-option` for a longer name option. As you may have already seen, options can have values assigned to them. A longer option name that takes a value can be assigned with a value using this `--long-option=value`, or that `--long-option value`. A short option configured to take values, on the other hand, may take a value through an assignment like this `-svalue`, or that `-s value`. 

As an example, if we wish to pass arguments to our little wiki example we could do the following:

    php wiki.php -i source --output destination

Which would yield the following values in the options array:

    Array
    (
        [input] => source
        [output] => destination
        [__executed] => wiki.php
    )

The following shell commands will also return the same output.

    php wiki.php -isource -odestination
    php wiki.php --input=source --output=destination

### Validation
Due to the validation of the required `output` option, if you should pass any set of arguments that fails to specify it, your script will be terminated, and the following output will be displayed.

    Values for the following options are required: output.
    Pass the --help option for more information about possible options.


## More on Parser output
You may have noticed the `__executed` key that was returned as part of the output. This key contains the name of the php script that invoked the parser. Other keys that may be returned include the `__args` key, which will contain an array of free standing arguments &mdash; useful for collecting filenames, especially since the terminal will expand any wildcards &mdask; and the `__command` which will contains any commands that were identified (more on that later). ClearIce doesn't consider stand alone options to be errors; you are expected to deal with them as you please.

As an example of the `__args` key, executing this ...

    php wiki.php -i source --output destination some free standing arguments

Will yeild an output containing ...

    Array
    (
        [input] => source
        [output] => destination
        [__args] => Array
            (
                [0] => some
                [1] => free
                [2] => standing
                [3] => arguments
            )

        [__executed] => wiki.php
    )


There's a convenience feature in ClearIce that allows you to preceed a group of short options with a single dash. For example if `a`, `b` and `c` are all valid options that do not take values, passing `-abc` would be equivalent to passing `-a -b -c`.





