<?php

namespace clearice\argparser;


/**
 * An implementation of the validator interface for validating parsed arguments and their definitions.
 *
 * @package clearice\argparser
 */
class Validator implements ValidatorInterface
{
    /**
     * Supported keys expected in the option array. This is used for validation./
     */
    const VALID_OPTION_KEYS = [
        'name', 'short_name', 'type', 'help', 'repeats',
        'default', 'value', 'required', 'command'
    ];

    /**
     * Validates the actual arguments passed to the shell to ensure they meet all the requirements of their definitions.
     *
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
     * Validates option definitions and throws exceptions for poorly defined options.
     *
     * @param $option
     * @param $commands
     * @throws InvalidArgumentDescriptionException
     * @throws UnknownCommandException
     */
    public function validateOption($option, $commands)
    {
        if (!(isset($option['name']) || isset($option['short_name']))) {
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
        if (isset($option['command'])) {
            foreach(is_array($option['command']) ? $option['command'] : [$option['command']] as $command) {
                if(!isset($commands[$command])) {
                    throw new UnknownCommandException("The command '{$command}' for option '{$option['name']}' has not been added to this parser.");
                }
            }
        }
    }

    /**
     * Validates command definitions and throws exceptions poorly defined commands.
     *
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
        if(!isset($command['help'])) {
            throw new InvalidArgumentDescriptionException("Please add a brief help message to your command description");
        }
        if (isset($commands[$command['name']])) {
            throw new CommandExistsException("Command ${command['name']} already exists.");
        }
    }
}