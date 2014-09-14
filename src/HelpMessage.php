<?php

namespace clearice;

class HelpMessage
{
    private $message = '';
    
    public function __construct($params)
    {
        $optionHelp = $this->getOptionsHelp($params['options']);
        
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
        
        foreach($sections as $i => $section)
        {
            $sections[$i] = implode("\n", $section);
        }
        
        $this->message = implode("\n", $sections);        
    }
    
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

