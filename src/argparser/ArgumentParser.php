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
     * @param $key
     * @param $value
     * @throws OptionExistsException
     */
    private function addToOptionArray($key, $value)
    {
        if(isset($value[$key]) && !isset($this->options[$value[$key]])) {
            $this->options[$value[$key]] = $value;
        } else if(isset($value[$key])) {
            throw new OptionExistsException("An argument option with name {$value['name']} already exists.");
        }
    }

    /**
     * Add an option to be parsed.
     * Arguments are presented as a structured array with the following possible keys.
     *
     *  name: The name of the option prefixed with a double dash --
     *  short_name: A shorter single character option prefixed with a single dash -
     *  type: Required for all options that take values. An option specified without a type is considered to be a boolean flag.
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
     * @param $arguments
     * @param $argPointer
     * @return array
     * @throws InvalidValueException
     */
    private function parseLongArgument($arguments, &$argPointer)
    {
        $name = substr($arguments[$argPointer], 2);
        $option = $this->options[$name];
        preg_match("/(?<option>[a-zA-Z_0-9-]+)(?<equal>=?)(?<value>.*)/", $name, $matches);

        if(isset($option['type'])) {
            $nextArgument = $arguments[$argPointer + 1];
            if($matches['equal'] === '=') {
                $value = $matches['value'];
            } else if ($nextArgument[0] != '-') {
                $value = $nextArgument;
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
                $output['__args'] = isset($output['__args']) ? $output['__args'] + [$arg] : [$arg];
            }
        }

        return $output;
    }

    public function getHelp()
    {

    }
}
