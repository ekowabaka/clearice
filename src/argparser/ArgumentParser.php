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
     * Add an option to be parsed.
     * Arguments are presented as a structured array with the following possible keys.
     *
     *  option: The name of the option prefixed with a double dash --
     *  short_option: A shorter single character option prefixed with a single dash -
     *  help: A help message for the option
     *
     * @param $option
     */
    public function addOption($option)
    {
        $this->options = $option;
    }

    /**
     * Parse arguments and return a structured array of options.
     *
     * @param array $arguments An optional array of arguments that would be parsed instead of those passed to the CLI.
     * @return array
     */
    public function parse($arguments = null)
    {
        global $argv;
        $arguments = $argv ?? $arguments;
        $options = [];

        for($argPointer = 0; $argPointer < count($arguments);) {
            $arg = $arguments[$argPointer];
            if(substr($arg, 0, 2) == "--") {
                $options = array_merge($options, $this->parseLongArgument($arguments, $argPointer));
            } else if ($arg[0] == '-') {
                $options = array_merge($options, $this->parseShortArgument($arguments, $argPointer));
            } else {
                $options['__args'] = isset($options['__args']) ? $options['__args'] + [$arg] : [$arg];
            }
        }

        return $arguments;
    }

    public function getHelp()
    {

    }
}
