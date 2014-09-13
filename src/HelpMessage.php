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
            'usage' => $usage,
            'options' => $optionHelp,
            'footnote' => array('', wordwrap($footnote), '')
        );
        
        foreach($sections as $i => $section)
        {
            $sections[$i] = implode("\n", $section);
        }
        
        $this->message = implode("\n", $sections);        
    }
    
    private function formatOptionHelp($option)
    {
        $optionHelp = array();
        $help = explode("\n", wordwrap($option['help'], 50));
        $valueHelp = '';
        $argumentPart = '';

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
    
    public function __toString()
    {
        return $this->message;
    }
}

