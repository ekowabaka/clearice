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

    /**
     * @var array
     */
    private $optionsCache = [];

    private $options = [];

    /**
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
        if(isset($value[$key]) && !isset($this->optionsCache[$value[$key]])) {
            $this->optionsCache[$value[$key]] = $value;
        } else if(isset($value[$key])) {
            throw new OptionExistsException("An argument option with $key {$value[$key]} already exists.");
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
     */
    public function addOption($option)
    {
        if(!isset($option['name'])) {
            throw new InvalidArgumentDescriptionException("Argument must have a name");
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
    private function parseLongArgument($arguments, &$argPointer)
    {
        $string = substr($arguments[$argPointer], 2);
        preg_match("/(?<name>[a-zA-Z_0-9-]+)(?<equal>=?)(?<value>.*)/", $string, $matches);
        $name = $matches['name'];
        $option = $this->optionsCache[$name];
        $value = true;

        if(isset($option['type'])) {
            if($matches['equal'] === '=') {
                $value = $matches['value'];
            } else {
                $value = $this->getNextValueOrFail($arguments, $argPointer, $name);
            }
        }

        return [$name, $this->castType($value, $option['type'] ?? null)];
    }

    /**
     * Parse a short argument that is prefixed with a single dash '-'
     *
     * @param $arguments
     * @param $argPointer
     * @return array
     * @throws InvalidValueException
     */
    public function parseShortArgument($arguments, &$argPointer)
    {
        $argument = $arguments[$argPointer];
        $key = substr($argument, 1, 1);
        $option = $this->optionsCache[$key];
        $value = true;

        if(isset($option['type'])) {
            if(substr($argument, 2) != "") {
                $value = substr($argument, 2);
            } else {
                $value = $this->getNextValueOrFail($arguments, $argPointer, $option['name']);
            }
        }

        return [$option['name'], $this->castType($value, $option['type'] ?? null)];
    }

    private function castType($value, $type)
    {
        switch($type) {
            case 'integer': return (int) $value;
            case 'float': return (float) $value;
            default: return $value;
        }
    }

    /**
     * @param $arguments
     * @return array
     * @throws InvalidValueException
     */
    private function parseArgumentArray($arguments)
    {
        $numArguments = count($arguments);
        $output = [];
        for($argPointer = 1; $argPointer < $numArguments; $argPointer++) {
            $arg = $arguments[$argPointer];
            if(substr($arg, 0, 2) == "--") {
                $argument = $this->parseLongArgument($arguments, $argPointer);
                $output[$argument[0]] = $argument[1];
            } else if ($arg[0] == '-') {
                $argument = $this->parseShortArgument($arguments, $argPointer);
                $output[$argument[0]] = $argument[1];
            } else {
                $output['__args'] = isset($output['__args']) ? array_merge($output['__args'], [$arg]) : [$arg];
            }
        }
        return $output;
    }

    private function maybeShowHelp($name, $output)
    {
        if(isset($output['help']) && $output['help']) {
            $this->helpGenerator->generate($name, $this->optionsCache, $this->description, $this->footer);
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
        $output = $this->parseArgumentArray($arguments);
        $this->maybeShowHelp($arguments[0], $output);
        return $output;
    }

    /**
     * @param $name
     * @param null $description
     * @param null $footer
     * @throws InvalidArgumentDescriptionException
     * @throws OptionExistsException
     */
    public function enableHelp($name, $description=null, $footer=null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->footer = $footer;

        $this->addOption(['name' => 'help', 'short_name' => 'h', 'help' => "get help on how to use this app $name"]);
    }
}
