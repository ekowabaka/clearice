
Parsing Command Line Arguments
==============================

[[_TOC_]]

ClearIce provides command line argument parsing for PHP applications. You can define the options and commands that will be passed to your application, and ClearICE will parse them for you, complete with error reporting and help generation. Option styles accepted through ClearIce are quite similar to what you would expect from most GNU style applications. Arguments passed to your application could be commands that your users may use to select specific modes in your application, options that your users can pass through flags, or stand-alone arguments that serve other purposes (such as providing file paths) in your application. 

You can access the argument parser through the `clearice\argparser\ArgumentParser` class.

````php
<?php

$argumentParser = new \clearice\argparser\ArgumentParser();

````

Defining argument options
------------------------
An `addOption` method in the `ArgumentParser` class allows you to define the options that can be parsed from your application's command line arguments. Each option's definition is passed as a single structured array to the `addOption` method. For example, the following listing shows how an instance of the argument parser can be configured to take two parameters.

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

The structured array can have specific parameters, and whenever an array with invalid parameters is passed, the `clearice\argparser\InvalidArgumentDescriptionException` is thrown. In all, there are eight different parameters for the options array. These are described in the sections below.

### `name`
This parameter specifies the name of the option. In addition, it also represents the flag that must be passed as a command line argument to activate the option. Because of its use as both an identifier and a flag, the name cannot contain spaces or wild card characters. For names, the parser only recognizes alpha numeric characters, hyphens, and dots. 

In our example above, the two options are named `output` and `input`. And when passing command arguments, the input option can be activated as `myprogram --input ...`, and similarly for the `output` option.

### `short_name`
This parameter defines a single, case-sensitive character that is typically used as a short-form synonym of an existing name parameter. As such, in the case of our example listing above, `o` is used as a short-form for `output` and `i` for `input`. Although mostly used as a short-form option, in cases where an option does not define a  `name`, the `short_name` parameter ends up being the identifier of the option. This means for any option definition to be valid, either the `name` or the `short_name` option must be set.

Users of your application can pass the short name on the command line by prefixing it with a single dash. If we re-visit our example listing above, if we intend to pass the input, we could simply use `myprogram -i`.

### `type`
When parsed, any option defined in ClearIce is assigned a specific value. The `type` parameter specifies the type of data that an option accepts. This type can either be set to `string`, `number` or `flag`. When set as `string` or `number`, the option is validated to contain a string or a number as specified. When set as a `flag`, however, the option doesn't take any values, and acts as a boolean flag that is set to true whenever the option is part of arguments passed or false when absent. By default any option acts as a `flag` unless the type is specified.

On the command line, users of your application can pass values for arguments either by assigning with equal signs, such as follows `command --input=/some/path`, by placing the value right after the argument, such as in `command --input /some/path`, and concatenated with the argument when it comes to short names `command --i/some/path`.

### `help`
The help parameter accepts a line of text that is rendered as the description for the option whenever the user requests for help through ClearICE's automated help system.

### `repeats`
By default, when an option's value is repeated in the command line, newer values ovewrite older ones. When the repeats parameter is set to `true`, however, arguments for options can be passed multiple times. All values passed will be combined into an array after parsing.

### `default`
The parameter provides the default value an option can have when the option's corresponding arguments are never passed on the command line.

### `value`
When displaying help for options, this value is used as an example value for options that take values. In case this is not supplied, a default value of `VALUE` is used instead

### `required`
Ensures that this option is always passed as part of the arguments. An error message is displayed, and the application is terminated when a required option is omitted. Whenever the required option is attached to a command group (see [[Defining Command Groups]]), the enforcement of the option takes place only when the command is activated. In the cases where an option has a default value, this parameter becomes uneccessary.


Parsing argument options
------------------------
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

When written out on the command line, the name for an option must be preceeded by a double dash `--`, and short options must be preceded with a single dash `-`. This means we can use `-s` for a short option, and `--long-option` for a longer name option. As you may have already seen, options can have values assigned to them. A longer option name that takes a value can be assigned with a value using this `--long-option=value`, or that `--long-option value`. A short option configured to take values, on the other hand, may take a value through an assignment like this `-svalue`, or that `-s value`. 

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





