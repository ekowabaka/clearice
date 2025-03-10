<?php

namespace clearice\argparser;


/**
 * For parsing arguments in ClearIce
 *
 * @package clearice\argparser
 */
class ArgumentParser
{
    /**
     * Description to put on top of the help message.
     * @var string
     */
    private $description;

    /**
     * A little message for the foot of the help message.
     * @var string
     */
    private $footer;

    /**
     * The name of the application.
     * @var string
     */
    private $name;

    /**
     * Commands that the application can execute.
     * @var array
     */
    private $commands = [];

    /**
     * A cache of all the options added.
     * The array keys represents a concatenation of the command and either the short or long name of the option. Elements
     * in this array will be the same as those in the options property. However, options that have both a short and long
     * name would appear twice.
     * @var array
     */
    private $optionsCache = [];

    /**
     * All the possible options for arguments.
     * @var array
     */
    private $options = [];

    /**
     * An instance of the help generator.
     * @var HelpMessageGenerator
     */
    private $helpGenerator;

    /**
     * An instance of the validator.
     * @var Validator
     */
    private $validator;

    /**
     * A reference to a function to be called for exitting the entire application.
     * @var callable
     */
    private $exitFunction;

    /**
     * Flag raised when help has been enabled.
     * @var bool
     */
    private $helpEnabled = false;

    /**
     * ArgumentParser constructor.
     *
     * @param ValidatorInterface $validator
     * @param HelpGeneratorInterface $helpWriter
     */
    public function __construct(?HelpGeneratorInterface $helpWriter = null, ?ValidatorInterface $validator = null)
    {
        $this->helpGenerator = $helpWriter ?? new HelpMessageGenerator();
        $this->validator = $validator ?? new Validator();
        $this->exitFunction = function ($code) { exit($code); };
    }

    /**
     * Add an option to the option cache for easy access through associative arrays.
     * The option cache associates arguments with their options.
     *
     * @param string $identifier
     * @param mixed $option
     * @throws OptionExistsException
     */
    private function addToOptionCache(string $identifier, $option) : void
    {
        if (!isset($option[$identifier])) {
            return;
        }
        $cacheKey = "{$option['command']}{$option[$identifier]}";
        if (!isset($this->optionsCache[$cacheKey])) {
            $this->optionsCache[$cacheKey] = $option;
        } else {
            throw new OptionExistsException(
                "An argument option with $identifier {$option['command']} {$option[$identifier]} already exists."
            );
        }
    }

    /**
     * @param string $command
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function retrieveOptionFromCache(string $command, string $name)
    {
        $key = $command . $name;
        if(isset($this->optionsCache[$key])) {
            return $this->optionsCache[$key];
        } else if(isset($this->optionsCache[$name]) && $this->optionsCache[$name]['command'] == "") {
            return $this->optionsCache[$name];
        } else{
            throw new InvalidArgumentException("Unknown option '$name'. Please run with `--help` for more information on valid options.");
        }
    }

    /**
     * Add an option to be parsed.
     * Arguments are presented as a structured array with the following possible keys.
     *
     *  name: The name of the option prefixed with a double dash --
     *  short_name: A shorter single character option prefixed with a single dash -
     *  type: Required for all options that take values. An option specified without a type is considered to be a
     *        boolean flag.
     *  repeats: A boolean value that states whether the option can be repeated or not. Repeatable options are returned
     *        as arrays.
     *  default: A default value for the option.
     *  help: A help message for the option
     *
     * @param array $option
     * @throws OptionExistsException
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function addOption(array $option): void
    {
        $this->validator->validateOption($option, $this->commands);
        $option['repeats'] = $option['repeats'] ?? false;
        // Save a copy of the original command definition, so it can be spread out if it's an array.
        $commands = $option['command'] ?? '';
        foreach(is_array($commands) ? $commands : [$commands] as $command) {
            $option['command'] = $command;
            $this->options[] = $option;
            $this->addToOptionCache('name', $option);
            $this->addToOptionCache('short_name', $option);
        }
    }

    /**
     * @param $arguments
     * @param $argPointer
     * @return mixed
     * @throws InvalidValueException
     */
    private function getNextValueOrFail($arguments, &$argPointer, $name)
    {
        if (isset($arguments[$argPointer + 1]) && $arguments[$argPointer + 1][0] != '-') {
            $argPointer++;
            return $arguments[$argPointer];
        } else {
            throw new InvalidValueException("A value must be passed along with argument $name.");
        }
    }

    /**
     * Parse a long argument that is prefixed with a double dash "--"
     *
     * @param $arguments
     * @param $argPointer
     * @throws InvalidValueException
     * @throws InvalidArgumentException
     */
    private function parseLongArgument($command, $arguments, &$argPointer, &$output)
    {
        $string = substr($arguments[$argPointer], 2);
        preg_match("/(?<name>[a-zA-Z_0-9-]+)(?<equal>=?)(?<value>.*)/", $string, $matches);
        $option = $this->retrieveOptionFromCache($command, $matches['name']);
        $value = true;

        if (isset($option['type'])) {
            if ($matches['equal'] === '=') {
                $value = $matches['value'];
            } else {
                $value = $this->getNextValueOrFail($arguments, $argPointer, $matches['name']);
            }
        }

        $this->assignValue($option, $output, $option['name'], $value);
    }

    /**
     * Parse a short argument that is prefixed with a single dash '-'
     *
     * @param $command
     * @param $arguments
     * @param $argPointer
     * @throws InvalidValueException
     * @throws InvalidArgumentException
     */
    private function parseShortArgument($command, $arguments, &$argPointer, &$output)
    {
        $argument = $arguments[$argPointer];
        $option = $this->retrieveOptionFromCache($command, substr($argument, 1, 1));
        $value = true;

        if (isset($option['type'])) {
            if (substr($argument, 2) != "") {
                $value = substr($argument, 2);
            } else {
                $value = $this->getNextValueOrFail($arguments, $argPointer, $option['name']);
            }
        }

        $this->assignValue($option, $output, $option['name'], $value);
    }

    private function assignValue($option, &$output, $key, $value)
    {
        if($option['repeats']) {
            $output[$key] = isset($output[$key]) ? array_merge($output[$key], [$value]) : [$value];
        } else {
            $output[$key] = $value;
        }
    }

    /**
     * @param $arguments
     * @param $argPointer
     * @param $output
     * @throws InvalidValueException
     * @throws InvalidArgumentException
     */
    private function parseArgumentArray($arguments, &$argPointer, &$output)
    {
        $numArguments = count($arguments);
        $command = $output['__command'] ?? '';
        for (; $argPointer < $numArguments; $argPointer++) {
            $arg = $arguments[$argPointer];
            if (substr($arg, 0, 2) == "--") {
                $this->parseLongArgument($command, $arguments, $argPointer, $output);
            } else if ($arg[0] == '-') {
                $this->parseShortArgument($command, $arguments, $argPointer, $output);
            } else {
                $output['__args'] = isset($output['__args']) ? array_merge($output['__args'], [$arg]) : [$arg];
            }
        }
    }

    /**
     * @param $output
     * @throws HelpMessageRequestedException
     */
    private function maybeShowHelp($output)
    {
        if ((isset($output['help']) && $output['help'] && $this->helpEnabled)) {
            print $this->getHelpMessage($output['__command'] ?? null);
            throw new HelpMessageRequestedException();
        }

        if(isset($output['__command']) && $output['__command'] == 'help' && $this->helpEnabled) {
            print $this->getHelpMessage($output['__args'][0] ?? null);
        }
    }

    private function parseCommand($arguments, &$argPointer, &$output)
    {
        if (count($arguments) > 1 && isset($this->commands[$arguments[$argPointer]])) {
            $output["__command"] = $arguments[$argPointer];
            $argPointer++;
        }
    }

    /**
     * @param $parsed
     */
    private function fillInDefaults(&$parsed)
    {
        foreach($this->options as $option) {
            if(!isset($parsed[$option['name']]) && isset($option['default']) && ($option['command'] == ($parsed['__command'] ?? "") || $option['command'] == '')) {
                $parsed[$option['name']] = $option['default'];
            }
        }
    }

    /**
     * A function called to exit the application whenever there's a parsing error or after requested help has been
     * displayed/
     *
     * @param callable $exit
     */
    public function setExitCallback(callable $exit)
    {
        $this->exitFunction = $exit;
    }

    /**
     * Parses command line arguments and return a structured array of options and their associated values.
     *
     * @param array $arguments An optional array of arguments that would be parsed instead of those passed to the CLI.
     * @return array
     * @throws InvalidValueException
     */
    public function parse($arguments = null)
    {
        try{
            global $argv;
            $arguments = $arguments ?? $argv;
            $argPointer = 1;
            $parsed = [];
            $this->name = $this->name ?? $arguments[0];
            $this->parseCommand($arguments, $argPointer, $parsed);
            $this->parseArgumentArray($arguments, $argPointer, $parsed);
            $this->maybeShowHelp($parsed);
            $this->validator->validateArguments($this->options, $parsed);
            $this->fillInDefaults($parsed);
            $parsed['__executed'] = $this->name;
            return $parsed;
        } catch (HelpMessageRequestedException $exception) {
            ($this->exitFunction)(0);
        } catch (InvalidArgumentException $exception) {
            print $exception->getMessage() . PHP_EOL;
            ($this->exitFunction)(1024);
        }
    }

    /**
     * Enables help messages so they are shown automatically when the appropriate argument (`--help` or `help`) is passed.
     * This method also allows you to optionally pass the name of the application, a description header for the help 
     * message and a footer.
     *
     * @param string $name The name of the application binary
     * @param string $description A description to be displayed on top of the help message
     * @param string $footer A footer message to be displayed after the help message
     *
     * @throws InvalidArgumentDescriptionException
     * @throws OptionExistsException
     * @throws UnknownCommandException
     */
    public function enableHelp(?string $description = null, ?string $footer = null, ?string $name = null) : void
    {
        global $argv;
        $this->name = $name ?? $argv[0];
        $this->description = $description;
        $this->footer = $footer;
        $this->helpEnabled = true;
        $this->addOption([
            'name' => 'help',
            'short_name' => 'h', 'help' => "display this help message"
        ]);
        if($this->commands) {
            $this->addCommand(['name' => 'help', 'help' => "display help for any command. Usage: {$this->name} help [command]"]);
            foreach($this->commands as $command) {
                $this->addOption([
                    'name' => 'help',
                    'help' => 'display this help message',
                    'command' => $command['name']
                ]);
            }
        }
    }

    public function getHelpMessage($command = '')
    {
        return $this->helpGenerator->generate(
            $this->name, $command ?? null,
            ['options' => $this->options, 'commands' => $this->commands],
            $this->description, $this->footer
        );
    }

    /**
     * @param $command
     * @throws CommandExistsException
     * @throws InvalidArgumentDescriptionException
     */
    public function addCommand($command)
    {
        $this->validator->validateCommand($command, $this->commands);
        $this->commands[$command['name']] = $command;
    }
}
