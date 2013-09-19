<?php
/**
 * A class for parsing command line arguments in PHP applications
 * 
 * ClearICE CLI Argument Parser
 * Copyright (c) 2012-2013 James Ekow Abaka Ainooson
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
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

/**
 * ClearICE class.
 */
class ClearICE
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
    private static $optionsMap = array();
    
    /**
     * An array of all the options that are available to the parser. Unlike the
     * ClearICE::$optionsMap parameter, this paramter just lists all the options
     * and their parameters.
     * 
     * @var array
     */
    private static $options = array();
    
    /**
     * Should the parser be strict or not. A strict parser would terminate the
     * application if it doesn't understand any options. A not-strict parser
     * would just return the unknown options it encountered and expect the
     * application to deal with it appropriately.
     * 
     * @var boolean
     */
    private static $strict = false;
    
    /**
     * A flag raised when the parser already has the automatic help option 
     * added.
     * 
     * @var boolean
     */
    private static $hasHelp;
    
    /**
     * The usage instructions for the application displayed as part of the
     * automatically generated help message.
     * 
     * @var array or string
     */
    private static $usage;
    
    /**
     * The description displayed on top of the help message just after the
     * usage instructions.
     * 
     * @var string
     */
    private static $description;
    
    /**
     * A footnote displayed at the bottom of the help message.
     * 
     * @var string
     */
    private static $footnote;
    
    private static $commands = array();
    
    public static function clearOptions()
    {
        self::$options = array();
        self::$optionsMap = array();
    }
    
    public static function addCommands()
    {
        self::$commands = array_merge(self::$commands, func_get_args());
    }

    /**
     * Add options to be recognized.
     */
    public static function addOptions()
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
            self::$options[] = $option;
            $command = isset($option['command']) ? $option['command'] : '__default__';
            if(isset($option['short'])) self::$optionsMap[$command][$option['short']] = $option;
            if(isset($option['long'])) self::$optionsMap[$command][$option['long']] = $option;
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
    public static function setStrict($strict)
    {
        self::$strict = $strict;
    }
    
    private static function parseShortOptions($shortOptionsString, &$options, &$unknowns)
    {
        $shortOption = $shortOptionsString[0];
        $remainder = substr($shortOptionsString, 1);
        $command = $options['command'];
            
        //@todo Whoops ... I need to simplify this someday
        if(isset(self::$optionsMap[$command][$shortOption]))
        {
            $key = isset(self::$optionsMap[$command][$shortOption]['long']) ? self::$optionsMap[$command][$shortOption]['long'] : $shortOption;
            if(isset(self::$optionsMap[$command][$shortOption]['has_value']))
            {
                if(self::$optionsMap[$command][$shortOption]['has_value'] === true)
                {
                    $options[$key] = $remainder;
                }
                else
                {
                    $options[$key] = true;
                    if(strlen($remainder) == 0) return;
                    self::parseShortOptions($remainder, $options, $unknowns);
                }
            }
            else
            {
                $options[$key] = true;
                if(strlen($remainder) == 0) return;
                self::parseShortOptions($remainder, $options, $unknowns);
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
                self::parseShortOptions($remainder, $options, $unknowns);
            }
        }
    }
    
    /**
     * Adds the two automatic help options.
     */
    public static function addHelp()
    {
        if(self::$hasHelp) return;
        self::addOptions(
            array(
                'short' => 'h',
                'long' => 'help',
                'help' => 'shows this help message'
            )
        );
        self::$hasHelp = true;
    }
    
    public static function setUsage($usage)
    {
        self::$usage = $usage;
    }

    public static function setDescription($description)
    {
        self::$description = $description;
    }
    
    public static function setFootnote($footnote)
    {
        self::$footnote = $footnote;
    }
    
    public static function getHelpMessage() 
    {
        global $argv;
        $helpMessage = wordwrap(self::$description);
        if($helpMessage != '') $helpMessage .= "\n";
        
        if(self::$usage != '' || is_array(self::$usage))
        {
            if($helpMessage != '') $helpMessage .= "\n";
            if(is_string(self::$usage))
            {
                $helpMessage .= "Usage:\n  {$argv[0]} " . self::$usage . "\n";
            }
            elseif (is_array(self::$usage)) 
            {
                $helpMessage .= "Usage:\n";
                foreach(self::$usage as $usage)
                {
                    $helpMessage .= "  {$argv[0]} $usage\n";
                }
            }
            $helpMessage .= "\n";
        }
        
        foreach (self::$options as $option)
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
            
            $argumentPart = sprintf("  -%s,  --%-19s ", $option['short'], "{$option['long']}{$valueHelp}");

            $helpMessage .= $argumentPart;

            if(strlen($argumentPart) == 29)
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
        
        if(self::$footnote != '')
        {
            $helpMessage .= "\n" . wordwrap(self::$footnote);
        }
        
        $helpMessage .= "\n";
        
        return $helpMessage;
    }


    public static function parse($arguments = false)
    {
        global $argv;
        
        if($arguments === false) $arguments = $argv;
        
        $executed = array_shift($arguments);
        $standAlones = array();
        $unknowns = array();
        $options = array();
        
        if(count(self::$commands) > 0)
        {
            $command = array_search($arguments[0], self::$commands);
         
        }
        else
        {
            $command = '__default__';
        }
        $options['command'] = $command;
        
        foreach($arguments as $argument)
        {
            if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
            {
                if(!isset(self::$optionsMap[$command][$matches['option']]))
                {
                    $unknowns[] = $matches['option'];
                    $options[$matches['option']] = $matches['value'];
                }
                else
                {
                    if(self::$optionsMap[$command][$matches['option']]['multi'] === true)
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
                if(!isset(self::$optionsMap[$command][$matches['option']]))
                {
                    $unknowns[] = $matches['option'];
                }
                $options[$matches['option']] = true;
            }
            else if(preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches))
            {
                self::parseShortOptions($matches['option'], $options, $unknowns);
            }
            else
            {
                $standAlones[] = $argument;
            }
        }
        
        if(self::$strict)
        {
            if(count($unknowns) > 0)
            {
                foreach($unknowns as $unknown)
                {
                    fputs(STDERR, "$executed: invalid option -- {$unknown}\n");
                }
                
                if(self::$hasHelp)
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
                echo self::getHelpMessage();
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
        
        if($command === '__default__') unset($options['command']);
        
        return $options;
    }
}


