<?php

namespace clearice\argparser;


/**
 * Class ArgumentParser
 *
 * @package clearice\argparser
 */
class ArgumentParser
{
    private $description;

    private $footer;

    private $name;

    private $commands = [];

    /**
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

    private $helpEnabled = false;

    public function __construct($helpWriter = null)
    {
        $this->helpGenerator = $helpWriter ?? new HelpMessageGenerator();
    }

    /**
     * Add a value to the available possible options for later parsing.
     *
     * @param string $key
     * @param array $value
     * @throws OptionExistsException
     */
    private function addToOptionCache(string $key, array $value) : void
    {
        if (!isset($value[$key])) {
            return;
        }
        $cacheKey = "${value['command']}${value[$key]}";
        if (!isset($this->optionsCache[$cacheKey])) {
            $this->optionsCache[$cacheKey] = $value;
        } else {
            throw new OptionExistsException(
                "An argument option with $key {$value['command']} {$value[$key]} already exists."
            );
        }
    }

    /**
     * @param array $option
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    private function validateOption($option) : void
    {
        if (!isset($option['name'])) {
            throw new InvalidArgumentDescriptionException("Argument must have a name");
        }
        if (isset($option['command']) && !isset($this->commands[$option['command']])) {
            throw new UnknownCommandException("The command {$option['command']} is unknown");
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
     * default: A default value for the option.
     *  help: A help message for the option
     *
     * @param array $option
     * @throws OptionExistsException
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function addOption(array $option): void
    {
        $this->validateOption($option);
        $option['command'] = $option['command'] ?? '';
        $option['repeats'] = $option['repeats'] ?? false;
        $this->options[] = $option;
        $this->addToOptionCache('name', $option);
        $this->addToOptionCache('short_name', $option);
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
     */
    private function parseLongArgument($command, $arguments, &$argPointer, &$output)
    {
        $string = substr($arguments[$argPointer], 2);
        preg_match("/(?<name>[a-zA-Z_0-9-]+)(?<equal>=?)(?<value>.*)/", $string, $matches);
        $name = $command . $matches['name'];
        $option = $this->optionsCache[$name];
        $value = true;

        if (isset($option['type'])) {
            if ($matches['equal'] === '=') {
                $value = $matches['value'];
            } else {
                $value = $this->getNextValueOrFail($arguments, $argPointer, $name);
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
     */
    public function parseShortArgument($command, $arguments, &$argPointer, &$output)
    {
        $argument = $arguments[$argPointer];
        $key = $command . substr($argument, 1, 1);
        $option = $this->optionsCache[$key];
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

    private function maybeShowHelp($output = [], $forced = false)
    {
        if ((isset($output['help']) && $output['help'] && $this->helpEnabled) || $forced) {
            return $this->helpGenerator->generate(
                $this->name, $output['command'] ?? null,
                ['options' => $this->options, 'commands' => $this->commands],
                $this->description, $this->footer
            );
        }
        return '';
    }

    public function parseCommand($arguments, &$argPointer, &$output)
    {
        if (count($arguments) > 1 && isset($this->commands[$arguments[$argPointer]])) {
            $output["__command"] = $arguments[$argPointer];
            $argPointer++;
        }
    }

    public function fillInDefaults(&$parsed)
    {
        foreach($this->options as $option) {
            if(!isset($parsed[$option['name']]) && isset($option['default'])) {
                $parsed[$option['name']] = $option['default'];
            }
        }
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
        global $argv;
        $arguments = $arguments ?? $argv;
        $argPointer = 1;
        $parsed = [];
        $this->name = $this->name ?? $arguments[0];
        $this->parseCommand($arguments, $argPointer, $parsed);
        $this->parseArgumentArray($arguments, $argPointer, $parsed);
        $this->fillInDefaults($parsed);
        $this->maybeShowHelp($parsed);
        return $parsed;
    }

    /**
     * Enables help messages so they show automatically.
     *
     * @param string $name
     * @param string $description
     * @param string $footer
     *
     * @throws InvalidArgumentDescriptionException
     * @throws OptionExistsException
     * @throws UnknownCommandException
     */
    public function enableHelp(string $description = null, string $footer = null, string $name = null) : void
    {
        $this->name = $name;
        $this->description = $description;
        $this->footer = $footer;
        $this->helpEnabled = true;
        $this->addOption(['name' => 'help', 'short_name' => 'h', 'help' => "display this help message"]);
    }

    public function getHelpMessage()
    {
        return $this->maybeShowHelp(null, true);
    }

    /**
     * @param $command
     * @throws CommandExistsException
     * @throws InvalidArgumentDescriptionException
     */
    public function addCommand($command)
    {
        if (!isset($command['name'])) {
            throw new InvalidArgumentDescriptionException("Command description must contain a name");
        }
        if (isset($this->commands[$command['name']])) {
            throw new CommandExistsException("Command ${command['name']} already exists.");
        }
        $this->commands[$command['name']] = $command;
    }
}
