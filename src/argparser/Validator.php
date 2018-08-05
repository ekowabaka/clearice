<?php

namespace clearice\argparser;


class Validator
{
    /**
     * @param $options
     * @throws InvalidArgumentException
     */
    public function validateArguments($options, $parsed)
    {
        $required = [];
        foreach($options as $option) {
            if(isset($option['required']) && $option['required'] && !isset($parsed[$option['name']]) && $option['command'] == ($parsed['__command'] ?? '')) {
                $required[] = $option['name'];
            }
        }

        if(!empty(($required))) {
            throw new InvalidArgumentException(
                sprintf(
                    "The following options are required%s: %s. Pass the --help option for more information about possible options.",
                    isset($parsed['__command']) ? " for the {$parsed['__command']} command" : "", implode(",", $required)
                )
            );
        }
    }

    /**
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function validateOption($option, $commands)
    {
        if (!isset($option['name'])) {
            throw new InvalidArgumentDescriptionException("Argument must have a name");
        }
        if (isset($option['command']) && !isset($commands[$option['command']])) {
            throw new UnknownCommandException("The command {$option['command']} is unknown");
        }
    }

    /**
     * @param $command
     * @param $commands
     * @throws CommandExistsException
     * @throws InvalidArgumentDescriptionException
     */
    public function validateCommand($command, $commands)
    {
        if (!isset($command['name'])) {
            throw new InvalidArgumentDescriptionException("Command description must contain a name");
        }
        if (isset($commands[$command['name']])) {
            throw new CommandExistsException("Command ${command['name']} already exists.");
        }
    }
}