<?php
/**
 * A class for parsing command line arguments in PHP applications
 * 
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


/**
 * ClearIce class.
 */
class ClearIce
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
    
    /**
     *
     * @var array
     */
    private $arguments = array();
    
    /**
     * Clear all the options that have been setup.
     */
    /*public function clearOptions()
    {
        $this->options = array();
        $this->optionsMap = array();
    }*/
    
    /**
     * Add commands for parsing. This method can take as many commands as possible.
     * 
     * @param String
     */
    public function addCommands()
    {
        $this->commands = array_merge($this->commands, func_get_args());
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
                $newOption = array();
                if(strlen($option) == 1) 
                {
                    $newOption['short'] = $option;
                }
                else
                {
                    $newOption['long'] = $option;
                }
                $option = $newOption;
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
     * @param string $shortOptionsString
     */
    private function parseShortOptions($shortOptionsString)
    {
        $shortOption = $shortOptionsString[0];
        $remainder = substr($shortOptionsString, 1);
        $command = $this->parsedOptions['__command__'];
            
        //@todo Whoops ... I need to simplify this someday
        if(isset($this->optionsMap[$command][$shortOption]))
        {
            $key = isset($this->optionsMap[$command][$shortOption]['long']) ? $this->optionsMap[$command][$shortOption]['long'] : $shortOption;
            if($this->optionsMap[$command][$shortOption]['has_value'] === true)
            {
                $this->parsedOptions[$key] = $remainder;
            }
            else
            {
                $this->parsedOptions[$key] = true;
                if(strlen($remainder) == 0) 
                    return;
                $this->parseShortOptions($remainder);
            }
        }
        else
        {
            $this->unknownOptions[] = $shortOption;
            $this->parsedOptions[$shortOption] = true;
            if(strlen($remainder) == 0) 
                return;
            else
                $this->parseShortOptions($remainder);
        }
    }
    
    /**
     * Adds the two automatic help options. A long one represented by --help and
     * a short one represented by -h.
     */
    public function addHelp()
    {
        if($this->hasHelp) return;
        $this->addOptions(
            array(
                'short' => 'h',
                'long' => 'help',
                'help' => 'shows this help message'
            )
        );
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
    
    private function getUsageMessage()
    {
        global $argv;
        $usageMessage = array('');
        
        if(is_string($this->usage))
        {
            $usageMessage[] = "Usage:\n  {$argv[0]} " . $this->usage;
        }
        elseif (is_array($this->usage)) 
        {
            $usageMessage[] = "Usage:";
            foreach($this->usage as $usage)
            {
                $usageMessage[] = "  {$argv[0]} $usage";
            }
        }
        $usageMessage[] = "";
        
        return $usageMessage;
    }
    
    private function formatOptionHelp($option)
    {
        $optionHelp = array();
        $help = explode("\n", wordwrap($option['help'], 50));

        if($option['has_value'])
        {
            $valueHelp = "=" . (isset($option['value']) ? $option['value'] : "VALUE");
        }

        if(isset($option['long']) && isset($option['short']))            
        {
            $argumentPart = sprintf(
                "  %s, %-22s ", "-{$option['short']}", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['long']))
        {
            $argumentPart = sprintf(
                "  %-27s", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['short']))
        {
            $argumentPart = sprintf(
                "  %-27s", "-{$option['short']}"
            );                
        }

        if(strlen($argumentPart) <= 29)
        {
            $optionHelp[] = $argumentPart . array_shift($help);
        }
        else
        {
            $optionHelp[] = $argumentPart;
        }

        foreach($help as $helpLine)
        {  
            $optionHelp[] = str_repeat(' ', 29) . "$helpLine" ;
        }        
        return $optionHelp;
    }
    
    /**
     * Returns the help message as a string.
     * 
     * @global type $argv
     * @return string
     */
    public function getHelpMessage() 
    {
        $optionHelp = array();
        foreach ($this->options as $option)
        {
            $optionHelp[] = implode("\n", $this->formatOptionHelp($option));
        }
        
        $sections = array(
            'description' => array(wordwrap($this->description)),
            'usage' => $this->getUsageMessage(),
            'options' => $optionHelp,
            'footnote' => array('', wordwrap($this->footnote), '')
        );
        
        foreach($sections as $i => $section)
        {
            $sections[$i] = implode("\n", $section);
        }
        
        
        return implode("\n", $sections);
    }
    
    /**
     * A function for getting answers to questions from users interractively.
     * @param $question The question you want to ask
     * @param $params An array of possible answers that this function should validate
     */
    public function getResponse($question, $params = array())
    {
        $prompt = $question;
        if(is_array($params['answers']))
        {
            if(count($params['answers']) > 0) {
                $prompt .= " (" . implode("/", $params['answers']) . ")";
            }
        }

        $this->output($prompt . " [{$params['default']}]: ");
        $response = str_replace(array("\n", "\r"),array("",""), $this->input());

        if($response == "" && $params['required'] === true && $params['default'] == '')
        {
            $this->output("A value is required.\n");
            return $this->getResponse($question, $params);
        }
        else if($response == "" && $params['required'] === true && $params['default'] != '')
        {
            return $params['default'];
        }
        else if($response == "")
        {
            return $params['default'];
        }
        else
        {
            if(count($params['answers']) == 0)
            {
                return $response;
            }
            foreach($params['answers'] as $answer)
            {
                if(strtolower($answer) == strtolower($response))
                {
                    return strtolower($answer);
                }
            }
            $this->output("Please provide a valid answer.\n");
            return $this->getResponse($question, $params);
        }
    }  
    
    /**
     * @param string $string
     */
    protected function output($string)
    {
        echo $string;
    }
    
    protected function input()
    {
        return fgets(STDIN);
    }
    
    /**
     * @param string $argument
     * @param string $value
     */
    private function parseLongOptionsWithValue($argument, $value)
    {
        $command = $this->parsedOptions['__command__'];
        if(!isset($this->optionsMap[$command][$argument]))
        {
            $this->unknownOptions[] = $argument;
            $this->parsedOptions[$argument] = $value;
        }
        else
        {
            if($this->optionsMap[$command][$argument]['multi'] === true)
            {
                $this->parsedOptions[$argument][] = $value;                                    
            }
            else
            {
                $this->parsedOptions[$argument] = $value;
            }
        }        
    }
    
    /**
     * @param string $argument
     */
    private function parseLongOptionsWithoutValue($argument)
    {
        $command = $this->parsedOptions['__command__'];
        if(!isset($this->optionsMap[$command][$argument]))
        {
            $this->unknownOptions[] = $argument;
        }
        $this->parsedOptions[$argument] = true;        
    }
    
    private function parseArgument($argument)
    {
        if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
        {
            $this->parseLongOptionsWithValue($matches['option'], $matches['value']);
        }
        else if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches))
        {
            $this->parseLongOptionsWithoutValue($matches['option']);
        }
        else if(preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches))
        {
            $this->parseShortOptions($matches['option']);
        }
        else
        {
            $this->standAlones[] = $argument;
        }        
    }
    
    private function getCommand()
    {
        if(count($this->commands) > 0)
        {
            $command = array_search($this->arguments, $this->commands);
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
    
    public function showStrictErrors($executed)
    {
        foreach($this->unknownOptions as $unknown)
        {
            fputs(STDERR, "$executed: invalid option -- {$unknown}\n");
        }

        if($this->hasHelp)
        {
            fputs(STDERR, "Try `$executed --help` for more information\n");
        }        
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
        
        foreach($this->arguments as $argument)
        {
            $this->parseArgument($argument);
        }
        
        if($this->strict && count($this->unknownOptions) > 0)
        {
            $this->showStrictErrors($executed);
        }
        
        if(isset($this->parsedOptions['help']))
        {
            echo $this->getHelpMessage();
        }
            
        if(count($this->standAlones)) $this->parsedOptions['stand_alones'] = $this->standAlones;
        if(count($this->unknownOptions)) $this->parsedOptions['unknowns'] = $this->unknownOptions;
        
        // Hide the __default__ command from the outside world
        if($this->parsedOptions['__command__'] == '__default__') 
        {
            unset($this->parsedOptions['__command__']);
        }
        
        return $this->parsedOptions;
    }
}


