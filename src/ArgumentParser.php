<?php

namespace clearice;

class ArgumentParser
{
    /**
     * A map of all the options the parser recognises. The map is actually an
     * array which associates short or long options with their appropriate 
     * parameters. Options which have both long and short versions would be
     * repeated. This structure is used to quickly find the paramters of an option
     * whether in the short form or long form.
     * 
     * @var array
     */
    private $optionsMap = array();
    
    /**
     * An array of all the options that are available to the parser. Unlike the
     * ClearIce::$optionsMap parameter, this paramter just lists all the options
     * and their parameters.
     * 
     * @var array
     */
    private $options = array();
    
    /**
     * Should the parser be strict or not. A strict parser would terminate the
     * application if it doesn't understand any options. A not-strict parser
     * would just return the unknown options it encountered and expect the
     * application to deal with it appropriately.
     * 
     * @var boolean
     */
    private $strict = false;
    
    /**
     * A flag raised when the parser already has the automatic help option 
     * added.
     * 
     * @var boolean
     */
    private $hasHelp;
    
    /**
     * The usage instructions for the application displayed as part of the
     * automatically generated help message.
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
     * An array of all the commands that the script can work with
     * @var array
     */
    private $commands = array();
    
    private $parsedOptions = array();
    private $unknownOptions = array();
    private $standAlones = array();
    private $longOptionParser;
    private $shortOptionParser;
    
    /**
     *
     * @var array
     */
    private $arguments = array();
    
    public function addUnknownOption($unknown)
    {
        $this->unknownOptions[] = $unknown;
    }
    
    public function addParsedOption($key, $value)
    {
        $this->parsedOptions[$key] = $value;
    }
    
    public function addParsedMultiOption($key, $value)
    {
        $this->parsedOptions[$key][] = $value;
    }
    
    /**
     * Parse the command line arguments and return a structured array which
     * represents the arguments which were interpreted by clearice.
     * 
     * @global type $argv
     * @return array
     */
    public function parse()
    {
        global $argv;
        $this->arguments = $argv;
        $executed = array_shift($this->arguments);

        $this->parsedOptions['__command__'] = $this->getCommand();
        $this->longOptionParser = new parsers\LongOptionParser($this, $this->optionsMap);
        $this->shortOptionParser = new parsers\ShortOptionParser($this, $this->optionsMap);
        
        foreach($this->arguments as $argument)
        {
            $this->parseArgument($argument);
        }
        
        $this->showStrictErrors($executed);
        $this->aggregateOptions();
        $this->showHelp();
        
        return $this->parsedOptions;
    }
    
    private function parseArgument($argument)
    {
        $success = FALSE;        
        if($this->parsedOptions['__command__'] != '__default__')
        {
            parsers\BaseParser::setLogUnknowns(false);
            $success = $this->getArgumentWithCommand($argument, $this->parsedOptions['__command__']);
        }

        if($success === false)
        {
            parsers\BaseParser::setLogUnknowns(true);                
            $this->getArgumentWithCommand($argument, '__default__');
        }        
    }
    
    private function aggregateOptions()
    {
        if(count($this->standAlones)) $this->parsedOptions['stand_alones'] = $this->standAlones;
        if(count($this->unknownOptions)) $this->parsedOptions['unknowns'] = $this->unknownOptions;  
        
        // Hide the __default__ command from the outside world
        if($this->parsedOptions['__command__'] == '__default__') 
        {
            unset($this->parsedOptions['__command__']);
        }        
    }
    
    private function showHelp()
    {
        if(isset($this->parsedOptions['help']))
        {
            ClearIce::output($this->getHelpMessage($this->parsedOptions['__command__']));
        } 
        if($this->parsedOptions['__command__'] == 'help')
        {
            ClearIce::output($this->getHelpMessage($this->standAlones[0]));
        }
        ClearIce::terminate();
    }

    private function showStrictErrors($executed)
    {
        if($this->strict && count($this->unknownOptions) > 0)
        {        
            foreach($this->unknownOptions as $unknown)
            {
                ClearIce::error("$executed: invalid option -- {$unknown}\n");
            }

            if($this->hasHelp)
            {
                ClearIce::error("Try `$executed --help` for more information\n");
            } 
        }            
    }
    
    private function getArgumentWithCommand($argument, $command)
    {
        $return = true;
        if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
        {
            $parser = $this->longOptionParser;
            $return = $parser->parse($matches['option'], $matches['value'], $command);
        }
        else if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches))
        {
            $parser = $this->longOptionParser;
            $return = $parser->parse($matches['option'], true, $command);
        }
        else if(preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches))
        {
            $parser = $this->shortOptionParser;
            $parser->parse($matches['option'], $command);
            $return = true;
        }
        else
        {
            $this->standAlones[] = $argument;
        }    
        return $return;
    }
    
    private function getCommand()
    {
        $commands = array_keys($this->commands);
        if(count($commands) > 0)
        {
            $command = array_search($this->arguments[0], $commands);
            if($command === false)
            {
                $command = '__default__';
            }
            else
            {
                $command = $this->arguments[0];
                array_shift($this->arguments);
            }
        }
        else
        {
            $command = '__default__';
        } 
        
        return $command;
    } 
    
    private function stringCommandToArray($command)
    {
        return array(
            'help' => '',
            'command' => $command
        );
    }
    
    
    /**
     * Add commands for parsing. This method can take as many commands as possible.
     * 
     * @param String
     */
    public function addCommands()
    {
        //$this->commands = array_merge($this->commands, func_get_args());
        foreach(func_get_args() as $command)
        {
            if(is_string($command))
            {
                $this->commands[$command] = $this->stringCommandToArray($command);
            }
            else
            {
                $this->commands[$command['command']] = $command;
            }
        }
    }
    
    private function stringOptionToArray($option)
    {
        $newOption = array();
        if(strlen($option) == 1) 
        {
            $newOption['short'] = $option;
        }
        else
        {
            $newOption['long'] = $option;
        }
        return $newOption;        
    }

    /**
     * Add options to be recognized. Options could either be strings or
     * structured arrays. Strings only define simple options. Structured arrays
     * describe options in deeper details.
     */
    public function addOptions()
    {
        $options = func_get_args();
        foreach($options as $option)
        {
            if(is_string($option))
            {
                $option = $this->stringOptionToArray($option);
            }
            $this->options[] = $option;
            $command = isset($option['command']) ? $option['command'] : '__default__';
            if(isset($option['short'])) $this->optionsMap[$command][$option['short']] = $option;
            if(isset($option['long'])) $this->optionsMap[$command][$option['long']] = $option;
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
      
        $this->addOptions(
            array(
                'short' => 'h',
                'long' => 'help',
                'help' => 'shows this help message'
            )
        );
        
        if(count($this->commands) > 0)
        {
            $this->addCommands(
                array(
                    'command' => 'help',
                    'help' => "displays specific help for any of the given commands.\nUsage: {$argv[0]} help [command]"
                )
            );
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
    public function getHelpMessage($command = '') 
    {
        return (string) new HelpMessage(
            array(
                'options' => $this->options, 
                'description' => $this->description, 
                'usage' => $this->usage,
                'commands' => $this->commands,
                'footnote' => $this->footnote,
                'command' => $command
            )
        );
    }    
}
