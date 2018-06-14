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

    public function __construct($helpWriter = null)
    {
        $this->helpGenerator = $helpWriter ?? new HelpMessageGenerator();
    }

    /**
     * Add a value to the available possible options for later parsing.
     *
     * @param $key
     * @param $value
     * @throws OptionExistsException
     */
    private function addToOptionCache($key, $value)
    {
        if(!isset($value[$key])) {
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
     * Add an option to be parsed.
     * Arguments are presented as a structured array with the following possible keys.
     *
     *  name: The name of the option prefixed with a double dash --
     *  short_name: A shorter single character option prefixed with a single dash -
     *  type: Required for all options that take values. An option specified without a type is considered to be a
     *        boolean flag.
     *  help: A help message for the option
     *
     * @param $option
     * @throws OptionExistsException
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function addOption($option)
    {
        if (!isset($option['name'])) {
            throw new InvalidArgumentDescriptionException("Argument must have a name");
        }
        if (isset($option['command']) && !isset($this->commands[$option['command']])) {
            throw new UnknownCommandException("The command {$option['command']} is unknown");
        } else if (!isset($option['command'])) {
            $option['command'] = '';
        }
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
     * @return array
     * @throws InvalidValueException
     */
    private function parseLongArgument($command, $arguments, &$argPointer)
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

        return [$option['name'], $this->castType($value, $option['type'] ?? null)];
    }

    /**
     * Parse a short argument that is prefixed with a single dash '-'
     *
     * @param $arguments
     * @param $argPointer
     * @return array
     * @throws InvalidValueException
     */
    public function parseShortArgument($command, $arguments, &$argPointer)
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

        return [$option['name'], $this->castType($value, $option['type'] ?? null)];
    }

    private function castType($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            default:
                return $value;
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
                $argument = $this->parseLongArgument($command, $arguments, $argPointer);
                $output[$argument[0]] = $argument[1];
            } else if ($arg[0] == '-') {
                $argument = $this->parseShortArgument($command, $arguments, $argPointer);
                $output[$argument[0]] = $argument[1];
            } else {
                $output['__args'] = isset($output['__args']) ? array_merge($output['__args'], [$arg]) : [$arg];
            }
        }
    }

    private function maybeShowHelp($name, $output)
    {
        if (isset($output['help']) && $output['help']) {
            $this->helpGenerator->generate(
                $name, $output['command'] ?? null,
                $this->optionsCache, $this->description, $this->footer
            );
        }
    }

    public function parseCommand($arguments, &$argPointer, &$output)
    {
        if (isset($this->commands[$arguments[$argPointer]])) {
            $output["__command"] = $arguments[$argPointer];
            $argPointer++;
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
        $this->parseCommand($arguments, $argPointer, $parsed);
        $this->parseArgumentArray($arguments, $argPointer, $parsed);
        $this->maybeShowHelp($arguments[0], $parsed);
        return $parsed;
    }

    /**
     * @param $name
     * @param null $description
     * @param null $footer
     * @throws InvalidArgumentDescriptionException
     * @throws OptionExistsException
     * @throws UnknownCommandException
     */
    public function enableHelp($name, $description = null, $footer = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->footer = $footer;

        $this->addOption(['name' => 'help', 'short_name' => 'h', 'help' => "get help on how to use this app $name"]);
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
