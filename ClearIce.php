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
 * @copyright Copyright 2012-s014 James Ekow Abaka Ainooson
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
     * @var array or string
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
    
    /**
     * Clear all the options that have been setup.
     */
    public function clearOptions()
    {
        $this->options = array();
        $this->optionsMap = array();
    }
    
    /**
     * Add commands for parsing. This method can take as many commands as possible.
     * 
     * <code>
     * ClearIce::addCommands('add', 'remove');
     * </code>
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
    
    private function parseShortOptions($shortOptionsString, &$options, &$unknowns)
    {
        $shortOption = $shortOptionsString[0];
        $remainder = substr($shortOptionsString, 1);
        $command = $options['__command__'];
            
        //@todo Whoops ... I need to simplify this someday
        if(isset($this->optionsMap[$command][$shortOption]))
        {
            $key = isset($this->optionsMap[$command][$shortOption]['long']) ? $this->optionsMap[$command][$shortOption]['long'] : $shortOption;
            if(isset($this->optionsMap[$command][$shortOption]['has_value']))
            {
                if($this->optionsMap[$command][$shortOption]['has_value'] === true)
                {
                    $options[$key] = $remainder;
                }
                else
                {
                    $options[$key] = true;
                    if(strlen($remainder) == 0) return;
                    $this->parseShortOptions($remainder, $options, $unknowns);
                }
            }
            else
            {
                $options[$key] = true;
                if(strlen($remainder) == 0) return;
                $this->parseShortOptions($remainder, $options, $unknowns);
            }
        }
        else
        {
            $unknowns[] = $shortOption;
            $options[$shortOption] = true;
            if(strlen($remainder) == 0) 
            {
                return;
            }
            else
            {
                $this->parseShortOptions($remainder, $options, $unknowns);
            }
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
     * @param string $usage
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
    public function getHelpMessage() 
    {
        global $argv;
        $helpMessage = wordwrap($this->description);
        if($helpMessage != '') $helpMessage .= "\n";
        
        if($this->usage != '' || is_array($this->usage))
        {
            if($helpMessage != '') $helpMessage .= "\n";
            if(is_string($this->usage))
            {
                $helpMessage .= "Usage:\n  {$argv[0]} " . $this->usage . "\n";
            }
            elseif (is_array($this->usage)) 
            {
                $helpMessage .= "Usage:\n";
                foreach($this->usage as $usage)
                {
                    $helpMessage .= "  {$argv[0]} $usage\n";
                }
            }
            $helpMessage .= "\n";
        }
        
        foreach ($this->options as $option)
        {
            $help = @explode("\n", wordwrap($option['help'], 50));
            if(isset($option['has_value']))
            {
                if($option['has_value'])
                {
                    $valueHelp = "=" . (isset($option['value']) ? $option['value'] : "VALUE");
                }
            }
            else 
            {
                $valueHelp = "";
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

            $helpMessage .= $argumentPart;

            if(strlen($argumentPart) <= 29)
            {
                $helpMessage .= array_shift($help) . "\n";
            }
            else
            {
                $helpMessage .= "\n";
            }
            
            foreach($help as $helpLine)
            {
               
                $helpMessage .= str_repeat(' ', 29) . "$helpLine\n" ;
            }
        }
        
        if($this->footnote != '')
        {
            $helpMessage .= "\n" . wordwrap($this->footnote);
        }
        
        $helpMessage .= "\n";
        
        return $helpMessage;
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
    
    protected function output($string)
    {
        echo $string;
    }
    
    protected function input()
    {
        return fgets(STDIN);
    }
    
    /**
     * Parse the command line arguments and return a structured array which
     * represents the arguments which were interpreted by clearice.
     * 
     * @global type $argv
     * @param type $arguments
     * @return array
     */
    public function parse($arguments = false)
    {
        global $argv;
        
        if($arguments === false) $arguments = $argv;
        
        $executed = array_shift($arguments);
        $standAlones = array();
        $unknowns = array();
        $options = array();
        
        if(count($this->commands) > 0)
        {
            $command = array_search($arguments[0], $this->commands);
            if($command === false)
            {
                $command = '__default__';
            }
            else
            {
                $command = $arguments[0];
                array_shift($arguments);
            }
            
        }
        else
        {
            $command = '__default__';
        }
        $options['__command__'] = $command;
        
        foreach($arguments as $argument)
        {
            if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
            {
                if(!isset($this->optionsMap[$command][$matches['option']]))
                {
                    $unknowns[] = $matches['option'];
                    $options[$matches['option']] = $matches['value'];
                }
                else
                {
                    if($this->optionsMap[$command][$matches['option']]['multi'] === true)
                    {
                        $options[$matches['option']][] = $matches['value'];                                    
                    }
                    else
                    {
                        $options[$matches['option']] = $matches['value'];
                    }
                }
                    
            }
            else if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches))
            {
                if(!isset($this->optionsMap[$command][$matches['option']]))
                {
                    $unknowns[] = $matches['option'];
                }
                $options[$matches['option']] = true;
            }
            else if(preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches))
            {
                $this->parseShortOptions($matches['option'], $options, $unknowns);
            }
            else
            {
                $standAlones[] = $argument;
            }
        }
        
        if($this->strict)
        {
            if(count($unknowns) > 0)
            {
                foreach($unknowns as $unknown)
                {
                    fputs(STDERR, "$executed: invalid option -- {$unknown}\n");
                }
                
                if($this->hasHelp)
                {
                    fputs(STDERR, "Try `$executed --help` for more information\n");
                }
                die();
            }
        }
        
        if(isset($options['help']))
        {
            if($options['help'])
            {
                echo $this->getHelpMessage();
                die();
            }
        }
        
        if(count($standAlones) > 0)
        {
            $options['stand_alones'] = $standAlones;
        }
        
        if(count($unknowns) > 0)
        {
            $options['unknowns'] = $unknowns;
        }
        
        // Hide the __default__ command from the outside world
        if($options['__command__'] == '__default__') unset($options['__command__']);
        
        return $options;
    }
}


