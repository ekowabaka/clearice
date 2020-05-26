<?php

namespace clearice\argparser;


class Validator implements ValidatorInterface
{
    const VALID_OPTION_KEYS = ['name', 'short_name', 'type', 'help', 'repeats', 'default', 'value', 'required'];

    /**
     * @param $options
     * @param $parsed
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
                    "Values for the following options are required%s: %s.\nPass the --help option for more information about possible options.",
                    isset($parsed['__command']) ? " for the {$parsed['__command']} command" : "", implode(",", $required)
                )
            );
        }
    }

    /**
     * @param $option
     * @param $commands
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function validateOption($option, $commands)
    {
        if (!isset($option['name']) || !isset($option['short_name'])) {
            throw new InvalidArgumentDescriptionException("An option must have either a name, a short_name or both.");
        }
        $name = $option['name'] ?? $option['short_name'];
        if(isset($option['default']) && isset($option['required'])) {
            throw new InvalidArgumentDescriptionException("A required option, {$name} cannot have a default value.");
        }
        foreach($option as $key => $value) {
            if(!in_array($key, self::VALID_OPTION_KEYS)) {
                throw new InvalidArgumentDescriptionException("Invalid key [$key] in option description for [{$name}]");
            }
        }
        if (isset($option['command']) && !isset($commands[$option['command']])) {
            throw new UnknownCommandException("The command '{$option['command']}' has not been added to this parser.");
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