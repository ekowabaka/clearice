<?php

namespace clearice\argparser;
use clearice\utils\ProgramControl;


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

    private $validator;

    private $programControl;

    private $helpEnabled = false;

    public function __construct($helpWriter = null, $programControl = null)
    {
        $this->helpGenerator = $helpWriter ?? new HelpMessageGenerator();
        $this->programControl = $programControl ?? new ProgramControl();
        $this->validator = new Validator();
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
     * @param string $key
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function retrieveOptionFromCache(string $key, string $name)
    {
        if(!isset($this->optionsCache[$key])) {
            throw new InvalidArgumentException("Unknown option '$name'. Please run with `--help` for more information on valid options.");
        }
        return $this->optionsCache[$key];
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
     * @throws InvalidArgumentException
     */
    private function parseLongArgument($command, $arguments, &$argPointer, &$output)
    {
        $string = substr($arguments[$argPointer], 2);
        preg_match("/(?<name>[a-zA-Z_0-9-]+)(?<equal>=?)(?<value>.*)/", $string, $matches);
        $key = $command . $matches['name'];
        $option = $this->retrieveOptionFromCache($key, $matches['name']);
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
        $key = $command . substr($argument, 1, 1);
        $option = $this->retrieveOptionFromCache($key, substr($argument, 1, 1));
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
     * @throws InvalidArgumentException
     */
    private function fillInDefaults(&$parsed)
    {
        foreach($this->options as $option) {
            if(!isset($parsed[$option['name']]) && isset($option['default']) && $option['command'] == ($parsed['__command'] ?? "")) {
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
            return $parsed;
        } catch (HelpMessageRequestedException $exception) {
            $this->programControl->quit();
        } catch (InvalidArgumentException $exception) {
            print $exception->getMessage() . PHP_EOL;
            $this->programControl->quit();
        }
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
        foreach($this->commands as $command) {
            $this->addOption(['name' => 'help', 'help' => 'display this help message', 'command' => $command['name']]);
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
