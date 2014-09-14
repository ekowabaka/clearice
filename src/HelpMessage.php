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
 * Class responsible for generating the help messages.
 */
class HelpMessage
{
    /**
     * The message which is usually generated by the constructor.
     * @var string
     */
    private $message = '';
    
    /**
     * The constructor for the HelpMessage class. This constructor does the work
     * of generating the help message.
     * @param array $params An associative array which contains the details needed
     *                      to help generate the help message.s
     */
    public function __construct($params)
    {
        $optionHelp = $this->getOptionsHelp($params['options']);
        
        // Build up sections and use them as the basis for the help message
        $sections['description'] = array(wordwrap($params['description']));
        $sections['usage'] = $this->getUsageMessage($params);
        
        if(count($params['commands']) > 0 && $params['command'] == '') 
        {
            $sections['commands'] = $this->getCommandsHelp($params['commands']); 
        }
        else if($params['command'] != '')
        {
            $sections['command_options'] = $this->getOptionsHelp(
                $params['options'], 
                $params['command'], 
                "Options for {$params['command']} command:"
            );
        }
        
        $sections['options'] = $optionHelp;
        $sections['footnote'] = array(wordwrap($params['footnote']), '');
        
        // Glue up all sections with newline characters to build the help
        // message
        foreach($sections as $i => $section)
        {
            $sections[$i] = implode("\n", $section);
        }
        
        $this->message = implode("\n", $sections);        
    }
    
    /**
     * The method runs through all the commands and generates formatted lines
     * of text to be used as the help message for all commands.
     * 
     * @param array $commands An array of associative arrays with infomation 
     *                        about all commands configured into ClearIce.
     * @return array
     */
    private function getCommandsHelp($commands)
    {
        $commandsHelp = array('Commands:');
        foreach ($commands as $command)
        {
            $commandsHelp[] = implode("\n", $this->formatCommandHelp($command));
        }
        $commandsHelp[] = '';    
        return $commandsHelp;
    }
    
    /**
     * The method runs through all the commands and generates formatted lines
     * of text to be used as the help message for options.
     * 
     * @param array $options    An array of associative arrays with infomation 
        *                       about all options configured into ClearIce.
     * @param string $command   All options returned would belong to the command 
     *                          stated in this argument.
     * @param string $title     A descriptive title for the header of this set 
     *                          of options.
     * @return array
     */    
    private function getOptionsHelp($options, $command = '', $title = 'Options:')
    {
        $optionHelp = array($title);
        foreach ($options as $option)
        {
            if($option['command'] != $command) continue;
            $optionHelp[] = implode("\n", $this->formatOptionHelp($option));
        }      
        $optionHelp[] = '';
        return $optionHelp;
    }
    
    private function formatValue($option)
    {
        if($option['has_value'])
        {
            return "=" . (isset($option['value']) ? $option['value'] : "VALUE");
        }
    }
    
    private function formatArgument($option)
    {
        $valueHelp = $this->formatValue($option);
        if(isset($option['long']) && isset($option['short']))            
        {
            $argumentHelp = sprintf(
                "  %s, %-22s ", "-{$option['short']}", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['long']))
        {
            $argumentHelp = sprintf(
                "  %-27s", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['short']))
        {
            $argumentHelp = sprintf(
                "  %-27s", "-{$option['short']}"
            );                
        }      
        return $argumentHelp;
    }
    
    private function wrapHelp($argumentPart, &$help, $minSize = 29)
    {
        if(strlen($argumentPart) <= $minSize)
        {
            return $argumentPart . array_shift($help);
        }
        else
        {
            return $argumentPart;
        }        
    }
    
    private function formatOptionHelp($option)
    {
        $optionHelp = array();
        $help = explode("\n", wordwrap($option['help'], 50));
        $argumentPart = $this->formatArgument($option);

        $optionHelp[] = $this->wrapHelp($argumentPart, $help);

        foreach($help as $helpLine)
        {  
            $optionHelp[] = str_repeat(' ', 29) . "$helpLine" ;
        }        
        return $optionHelp;
    }  
    
    private function formatCommandHelp($command)
    {
        $commandHelp = array();
        $help = explode("\n", wordwrap($command['help'], 59));
        $commandHelp[] = $this->wrapHelp(sprintf("% -20s", $command['command']), $help, 20);
        foreach($help as $helpLine)
        {
            $commandHelp[] = str_repeat(' ', 20) . $helpLine;
        }
        return $commandHelp;
    }
    
    private function getUsageMessage($params)
    {
        global $argv;
        $usageMessage = array('');
        
        if($params['command'] != '')
        {
            if(isset($params['commands'][$params['command']]['usage']))
            {
                $usage = $params['commands'][$params['command']]['usage'];
            }
            else
            {
                $usage = "{$params['command']} [options]..";
            }
        }
        else
        {
            $usage = $params['usage'];
        }
        
        if(is_string($usage))
        {
            $usageMessage[] = "Usage:\n  {$argv[0]} " . $usage;
        }
        elseif (is_array($usage)) 
        {
            $usageMessage[] = "Usage:";
            foreach($usage as $usage)
            {
                $usageMessage[] = "  {$argv[0]} {$usage}";
            }
        }
        $usageMessage[] = "";
        
        return $usageMessage;
    }    
    
    public function __toString()
    {
        return $this->message;
    }
}

