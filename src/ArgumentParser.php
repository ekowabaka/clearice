<?php

/*
 * ClearIce CLI Argument Parser
 * Copyright (c) 2012-2014 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2012-2014 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace clearice;

/**
 * Class responsible for parsing individual arguments.
 * @internal Composed into the static ClearIce class
 */
class ArgumentParser
{

    /**
     * An array of all the options that are available to the parser. Unlike the
     * ClearIce::$optionsMap parameter, this paramter just lists all the options
     * and their parameters. Any option added through the ArgumentParser::addOptions()
     * parameter is just appended to this array.
     * 
     * @var \clearice\Options
     */
    private $options = [];

    /**
     * Specifies whether the parser should be strict about errors or not. 
     * 
     * @var boolean
     */
    private $strict = false;

    /**
     * A flag raised when the parser already has the automatic help option 
     * added. This is used to prevent multiple help options.
     * 
     * @var boolean
     */
    private $hasHelp;

    /**
     * The usage instructions for the application displayed as part of the
     * automatically generated help message. This message is usually printed
     * after the description.
     * 
     * @var array|string
     */
    private $usage;

    /**
     * The description displayed on top of the help message just after the
     * usage instructions.
     * 
     * @var string
     */
    private $description;

    /**
     * A footnote displayed at the bottom of the help message.
     * 
     * @var string
     */
    private $footnote;

    /**
     * An array of all the commands that the script can work with.
     * @var array
     */
    private $commands = [];
    private $groups = [];

    /**
     * The current command being run.
     * @var string
     */
    private $command;

    /**
     * Holds all the options that have already been parsed and recognized.
     * @var array
     */
    private $parsedOptions = [];

    /**
     * Holds all the options that have been parsed but are unknown.
     * @var array
     */
    private $unknownOptions = [];

    /**
     * Options that are standing alone.
     * @var array
     */
    private $standAlones = [];

    /**
     * An instance of the long option parser used for the parsing of long options
     * which are preceed with a double dash "--".
     * @var \clearice\parsers\LongOptionParser
     */
    private $longOptionParser;

    /**
     * An instance of the short option parser used for the parsing of short optoins
     * which are preceeded with a single dash "-".
     * @var \clearice\parsers\ShortOptionParser
     */
    private $shortOptionParser;

    /**
     * The arguments that were passed through the command line to the script or
     * application.
     * 
     * @var array
     */
    private $arguments = [];
    private $argumentPointer;
    private $io;

    public function __construct(ConsoleIO $io)
    {
        $this->options = new Options();
        $this->io = $io;
    }

    /**
     * Adds an unknown option to the list of unknown options currently held in
     * the parser.
     * 
     * @param string $unknown
     */
    public function addUnknownOption($unknown)
    {
        $this->unknownOptions[] = $unknown;
    }

    /**
     * Adds a known parsed option to the list of parsed options currently held
     * in the parser.
     * @param string $key The option.
     * @param string $value The value asigned to the option.
     */
    public function addParsedOption($key, $value)
    {
        $this->parsedOptions[$key] = $value;
    }

    /**
     * Adds a new value of a multi option.
     * @param string $key The option.
     * @param string $value The value to be appended to the list.
     */
    public function addParsedMultiOption($key, $value)
    {
        $this->parsedOptions[$key][] = $value;
    }

    /**
     * Parse the command line arguments and return a structured array which
     * represents the options which were interpreted by ClearIce. The array
     * returned has the following structure.
     * 
     * 
     * @global type $argv
     * @return array
     */
    public function parse()
    {
        global $argv;
        $this->arguments = $argv;
        $executed = array_shift($this->arguments);
        $this->command = $this->getCommand();

        $this->parsedOptions = $this->options->getDefaults($this->command);
        $this->parsedOptions['__command__'] = $this->command;
        if(isset($this->commands[$this->command]['data'])) {
            $this->parsedOptions['data'] = $this->commands[$this->command]['data'];
        }
        $this->longOptionParser = new parsers\LongOptionParser($this, $this->options->getMap());
        $this->shortOptionParser = new parsers\ShortOptionParser($this, $this->options->getMap());

        $numArguments = count($this->arguments);
        for ($this->argumentPointer = 0; $this->argumentPointer < $numArguments; $this->argumentPointer++) {
            $this->parseArgument($this->arguments[$this->argumentPointer]);
        }

        $this->showStrictErrors($executed);
        $this->aggregateOptions();
        $this->showHelp();

        return $this->parsedOptions;
    }

    public function getLookAheadArgument()
    {
        return $this->arguments[$this->argumentPointer + 1];
    }

    public function moveToNextArgument()
    {
        $this->argumentPointer++;
    }

    private function parseArgument($argument)
    {
        $success = false;
        // Try to parse the argument for a command
        if ($this->parsedOptions['__command__'] != '__default__') {
            parsers\BaseParser::setLogUnknowns(false);
            $success = $this->getArgumentWithCommand($argument, $this->parsedOptions['__command__']);
        }

        // If not succesful get argument for the __default__ command.
        if ($success === false) {
            parsers\BaseParser::setLogUnknowns(true);
            $this->getArgumentWithCommand($argument, '__default__');
        }
    }

    private function aggregateOptions()
    {
        if (count($this->standAlones)) {
            $this->parsedOptions['stand_alones'] = $this->standAlones;
        }
        if (count($this->unknownOptions)) {
            $this->parsedOptions['unknowns'] = $this->unknownOptions;
        }

        // Hide the __default__ command from the outside world
        if ($this->parsedOptions['__command__'] == '__default__') {
            unset($this->parsedOptions['__command__']);
        }
    }

    private function showHelp()
    {
        if (isset($this->parsedOptions['help'])) {
            $this->io->output($this->getHelpMessage(
                    isset($this->parsedOptions['__command__']) ?
                        $this->parsedOptions['__command__'] : null
                )
            );
        }
        if ($this->command == 'help') {
            $this->io->output($this->getHelpMessage($this->standAlones[0]));
        }
    }

    private function showStrictErrors($executed)
    {
        if ($this->strict && count($this->unknownOptions) > 0) {
            foreach ($this->unknownOptions as $unknown) {
                $this->io->error("$executed: invalid option -- {$unknown}\n");
            }
            if ($this->hasHelp) {
                $this->io->error("Try `$executed --help` for more information\n");
            }
        }
    }

    private function getArgumentWithCommand($argument, $command)
    {
        $return = true;
        if (preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches)) {
            $return = $this->longOptionParser->parse($matches['option'], $matches['value'], $command);
        } else if (preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches)) {
            $return = $this->longOptionParser->parse($matches['option'], true, $command);
        } else if (preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches)) {
            $this->shortOptionParser->parse($matches['option'], $command);
            $return = true;
        } else {
            $this->standAlones[] = $argument;
        }
        return $return;
    }

    private function getCommand()
    {
        $commands = array_keys($this->commands);
        if (count($commands) > 0 && count($this->arguments) > 0) {
            $command = array_search($this->arguments[0], $commands);
            if ($command === false) {
                $command = '__default__';
            } else {
                $command = $this->arguments[0];
                array_shift($this->arguments);
            }
        } else {
            $command = '__default__';
        }
        return $command;
    }

    private function stringCommandToArray($command)
    {
        return ['help' => '', 'command' => $command];
    }

    /**
     * Add commands for parsing. 
     * This method takes many arguments with each representing a unique command. 
     * 
     * @param String
     * @see ClearIce::addCommands()
     */
    public function addCommands($commands)
    {
        foreach ($commands as $command) {
            if (is_string($command)) {
                $command = $this->stringCommandToArray($command);
            }
            $this->commands[$command['command']] = $command;
        }
    }

    /**
     * Add options to be recognized. 
     * Options could either be strings or structured arrays. Strings define 
     * simple options. Structured arrays describe options in deeper details.
     */
    public function addOptions($options)
    {
        $this->options->add($options);
    }

    public function addGroups($groups)
    {
        foreach ($groups as $group) {
            $this->groups[$group['group']] = $group;
        }
    }

    /**
     * Sets whether the parser should be strict or not. A strict parser would 
     * terminate the application if it doesn't understand any options. A 
     * not-strict parser would just return the unknown options it encountered 
     * and expect the application to deal with it appropriately.     
     * 
     * @param boolean $strict A boolean value for the strictness state
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
    }

    /**
     * Adds the two automatic help options. A long one represented by --help and
     * a short one represented by -h.
     */
    public function addHelp()
    {
        global $argv;

        $this->addOptions([['short' => 'h', 'long' => 'help', 'help' => 'Shows this help message']]);

        if (count($this->commands) > 0) {
            $this->addCommands([[
                'command' => 'help',
                'help' => "Displays specific help for any of the given commands.\nusage: {$argv[0]} help [command]"  
            ]]);
        }

        $this->hasHelp = true;
    }

    /**
     * Set the usage text which forms part of the help text.
     * @param string|array $usage
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
    }

    /**
     * Set the description text shown on top of the help text.
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Set the footnote text shown at the bottom of the help text.
     * @param string $footnote
     */
    public function setFootnote($footnote)
    {
        $this->footnote = $footnote;
    }

    /**
     * Returns the help message as a string.
     * 
     * @global type $argv
     * @return string
     */
    public function getHelpMessage($command = null)
    {
        return (string) new HelpMessage([
            'options' => $this->options,
            'description' => $this->description,
            'usage' => $this->usage,
            'commands' => $this->commands,
            'footnote' => $this->footnote,
            'command' => $command,
            'groups' => $this->groups
        ]);
    }
    
    public function getIO() : ConsoleIO
    {
        return $this->io;
    }

}
