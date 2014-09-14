<?php

namespace clearice;

class HelpMessage
{
    private $message = '';
    
    public function __construct($options, $description, $usage, $footnote)
    {
        $optionHelp = array();
        foreach ($options as $option)
        {
            $optionHelp[] = implode("\n", $this->formatOptionHelp($option));
        }
        
        $sections = array(
            'description' => array(wordwrap($description)),
            'usage' => $this->getUsageMessage($usage),
            'options' => $optionHelp,
            'footnote' => array('', wordwrap($footnote), '')
        );
        
        foreach($sections as $i => $section)
        {
            $sections[$i] = implode("\n", $section);
        }
        
        $this->message = implode("\n", $sections);        
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
    
    private function wrapHelp($argumentPart, &$help)
    {
        if(strlen($argumentPart) <= 29)
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
    
    private function getUsageMessage($usage)
    {
        global $argv;
        $usageMessage = array('');
        
        if(is_string($usage))
        {
            $usageMessage[] = "Usage:\n  {$argv[0]} " . $usage;
        }
        elseif (is_array($usage)) 
        {
            $usageMessage[] = "Usage:";
            foreach($usage as $usage)
            {
                $usageMessage[] = "  {$argv[0]} $usage";
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

