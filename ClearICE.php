<?php

class ClearICE
{
    private static $optionsMap = array();
    private static $options = array();
    private static $strict = false;
    private static $hasHelp;
    private static $usage;
    private static $description;
    private static $footnote;

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
            if(isset($option['short'])) self::$optionsMap[$option['short']] = $option;
            if(isset($option['long'])) self::$optionsMap[$option['long']] = $option;
        }
    }
    
    public static function setStrict($strict)
    {
        self::$strict = $strict;
    }
    
    private static function parseShortOptions($shortOptionsString, &$options)
    {
        $shortOption = $shortOptionsString[0];
        $remainder = substr($shortOptionsString, 1);
            
        //@todo Whoops ... I need to simplify this someday
        if(isset(self::$optionsMap[$shortOption]))
        {
            $key = isset(self::$optionsMap[$shortOption]['long']) ? self::$optionsMap[$shortOption]['long'] : $shortOption;
            if(isset(self::$optionsMap[$shortOption]['has_value']))
            {
                if(self::$optionsMap[$shortOption]['has_value'] === true)
                {
                    $options[$key] = $remainder;
                }
                else
                {
                    $options[$key] = true;
                }
            }
            else
            {
                $options[$key] = true;
                if(strlen($remainder) == 0) return;
                self::parseShortOptions($remainder, $options);
            }
        }
        else
        {
            $options['unknowns'][] = $shortOption;
            if(strlen($remainder) == 0) 
            {
                return;
            }
            else
            {
                self::parseShortOptions($remainder, $options);
            }
        }
    }
    
    public static function addHelp()
    {
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
        $helpMessage = wordwrap(self::$description);
        foreach (self::$options as $option)
        {
            $help = @explode("\n", wordwrap($option['help'], 50));
            $argumentPart = sprintf("  -%s,  --%-19s ", $option['short'], $option['long']);

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
        $helpMessage .= "\n";
        
        return $helpMessage;
    }


    public static function parse($arguments = false)
    {
        global $argv;
        
        if($arguments === false) $arguments = $argv;
        
        $standAlones = array();
        $shorts = array();
        $options = array(
            'unknowns' => array(),
            'cli-standalones' => array()
        );
        
        foreach($arguments as $argument)
        {
            if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
            {
                if(!isset(self::$optionsMap[$matches['option']]))
                {
                    $options['unknowns'][] = $matches['option'];
                }
                $options[$matches['option']] = $matches['value'];
                    
            }
            else if(preg_match('/^(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches))
            {
                if(!isset(self::$optionsMap[$matches['option']]))
                {
                    $options['unknowns'][] = $matches['option'];
                }
                $options[$matches['option']] = true;
            }
            else if(preg_match('/^(-)(?<option>[a-zA-z0-9](.*))/i', $argument, $matches))
            {
                self::parseShortOptions($matches['option'], $options);
            }
            else
            {
                $standAlones[] = $argument;
            }
        }
        
        $options['cli-standalones'] = $standAlones;
        
        if(self::$strict)
        {
            if(count($options['unknowns']) > 0)
            {
                foreach($options['unknowns'] as $unknown)
                {
                    fputs(STDERR, "{$arguments[0]}: invalid option -- {$unknown}\n");
                }
                fputs(STDERR, "Try `{$arguments[0]} --help` for more information\n");
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
        
        return $options;
    }
}


