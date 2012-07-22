<?php

class ClearICE
{
    private static $shortOptions = array();
    private static $knowOptions = array();
    
    public static function addKnownOption()
    {
        $options = func_get_args();
        
        foreach($options as $option)
        {
            if(isset($option['short']))
            {
                self::$shortOptions[$option['short']] = array(
                    'option' => $option['option'],
                    'help' => $option['help']
                );
            }
            
            self::$knowOptions[$option['option']] = $option;
        }
    }
    
    public static function parse($arguments)
    {
        $standAlones = array();
        $shorts = array();
        $options = array();
        
        foreach($arguments as $argument)
        {
            if(preg_match('/(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)(=)(?<value>.*)/i', $argument, $matches))
            {
                $options[$matches['option']] = $matches['value'];
            }
            else if(preg_match('/(--)(?<option>[a-zA-z][0-9a-zA-Z-_\.]*)/i', $argument, $matches))
            {
                $options[$matches['option']] = true;
            }
            else if(preg_match('/(-)(?<short>[a-zA-z0-9])(?<value>.*)/i', $argument, $matches))
            {
                if(isset(self::$shortOptions[$matches['short']]))
                {
                    if($matches['value'] == '')
                    {
                        $options[self::$shortOptions[$matches['short']]['option']] = true;
                    }
                    else
                    {
                        $options[self::$shortOptions[$matches['short']]['option']] = $matches['value'];
                    }
                }
            }
        }
        
        return $options;
    }
}

