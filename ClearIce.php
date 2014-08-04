<?php
/**
 * A class for parsing command line arguments in PHP applications
 * 
 * ClearIce CLI Argument Parser
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
    private static $optionsMap = array();
    
    /**
     * An array of all the options that are available to the parser. Unlike the
     * ClearIce::$optionsMap parameter, this paramter just lists all the options
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
    
    /**
     * An array of all the commands that the script can work with
     * @var array
     */
    private static $commands = array();
    
    /**
     * Clear all the options that have been setup.
     */
    public static function clearOptions()
    {
        self::$options = array();
        self::$optionsMap = array();
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
    public static function addCommands()
    {
        self::$commands = array_merge(self::$commands, func_get_args());
    }

    /**
     * Add options to be recognized. Options could either be strings or
     * structured arrays. Strings only define simple options. Structured arrays
     * describe options in deeper details.
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
        $command = $options['__command__'];
            
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
     * Adds the two automatic help options. A long one represented by --help and
     * a short one represented by -h.
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
    
    /**
     * Set the usage text which forms part of the help text.
     * @param string $usage
     */
    public static function setUsage($usage)
    {
        self::$usage = $usage;
    }

    /**
     * Set the description text shown on top of the help text.
     * @param string $description
     */
    public static function setDescription($description)
    {
        self::$description = $description;
    }
    
    /**
     * Set the footnote text shown at the bottom of the help text.
     * @param string $footnote
     */
    public static function setFootnote($footnote)
    {
        self::$footnote = $footnote;
    }
    
    /**
     * Returns the help message as a string.
     * 
     * @global type $argv
     * @return string
     */
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
        
        if(self::$footnote != '')
        {
            $helpMessage .= "\n" . wordwrap(self::$footnote);
        }
        
        $helpMessage .= "\n";
        
        return $helpMessage;
    }
    
    /**
     * A function for getting answers to questions from users interractively.
     * @param $question The question you want to ask
     * @param $answers An array of possible answers that this function should validate
     * @param $default The default answer this function should assume for the user.
     * @param $notNull Is the answer required
     */
    public static function getResponse($question, $default=null, $answers=null, $required = false)
    {
        echo $question;
        if(is_array($answers))
        {
            if(count($answers) > 0) {
                echo " (" . implode("/", $answers) . ")";
            }
        }

        echo " [$default]: ";
        $response = str_replace(array("\n", "\r"),array("",""),fgets(STDIN));

        if($response == "" && $required === true && $default == '')
        {
            echo "A value is required.\n";
            return self::getResponse($question, $answers, $default, $notNull);
        }
        else if($response == "" && $required === true && $default != '')
        {
            return $default;
        }
        else if($response == "")
        {
            return $default;
        }
        else
        {
            if(count($answers) == 0)
            {
                return $response;
            }
            foreach($answers as $answer)
            {
                if(strtolower($answer) == strtolower($response))
                {
                    return strtolower($answer);
                }
            }
            echo "Please provide a valid answer.\n";
            return getResponse($question, $answers, $default, $notNull);
        }
    }    
    
    /**
     * Parse the command line arguments and return a structured array which
     * represents the arguments which were interpreted by clearice.
     * 
     * @global type $argv
     * @param type $arguments
     * @return array
     */
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
        
        // Hide the __default__ command from the outside world
        if($options['__command__'] == '__default__') unset($options['__command__']);
        
        return $options;
    }
}


