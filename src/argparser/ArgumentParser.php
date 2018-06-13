<?php

namespace clearice\argparser;


class ArgumentParser
{
    private $description;
    private $footer;
    private $options = [];

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    /**
     * Add a value to the available possible options for later parsing.
     * @param $key
     * @param $value
     * @throws OptionExistsException
     */
    private function addToOptionArray($key, $value)
    {
        if(isset($value[$key]) && !isset($this->options[$value[$key]])) {
            $this->options[$value[$key]] = $value;
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
        $this->addToOptionArray('name', $option);
        $this->addToOptionArray('short_name', $option);
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
        $option = $this->options[$name];

        if(isset($option['type'])) {
            if($matches['equal'] === '=') {
                $value = $matches['value'];
            } else if (isset($arguments[$argPointer + 1]) && $arguments[$argPointer + 1][0] != '-') {
                $value = $arguments[$argPointer + 1];
                $argPointer++;
            } else {
                throw new InvalidValueException("A value must be passed along with argument $name.");
            }
        } else {
            $value = true;
        }

        return [$name => $value];
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
        $option = $this->options[$key];

        if(isset($option['type'])) {
            if(substr($argument, 2) != "") {
                $value = substr($argument, 2);
            } else if(isset($arguments[$argPointer + 1]) && $arguments[$argPointer + 1][0] != '-') {
                $value = $arguments[$argPointer + 1];
                $argPointer++;
            } else {
                throw new InvalidValueException("A value must be passed along with argument ${option['name']}");
            }
        } else {
            $value = true;
        }

        return [$option['name'] => $value];
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
        $numArguments = count($arguments);
        $output = [];

        for($argPointer = 1; $argPointer < $numArguments; $argPointer++) {
            $arg = $arguments[$argPointer];
            if(substr($arg, 0, 2) == "--") {
                $output = array_merge($output, $this->parseLongArgument($arguments, $argPointer));
            } else if ($arg[0] == '-') {
                $output = array_merge($output, $this->parseShortArgument($arguments, $argPointer));
            } else {
                $output['__args'] = isset($output['__args']) ? array_merge($output['__args'], [$arg]) : [$arg];
            }
        }

        return $output;
    }

    public function getHelp()
    {

    }
}
