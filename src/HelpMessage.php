<?php

namespace clearice;

class HelpMessage
{
    private $message = '';
    private $line;
    
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
            return sprintf(
                "  %s, %-22s ", "-{$option['short']}", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['long']))
        {
            return sprintf(
                "  %-27s", "--{$option['long']}$valueHelp"
            );
        }
        else if(isset($option['short']))
        {
            return sprintf(
                "  %-27s", "-{$option['short']}"
            );                
        }        
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
    
    public function __toString()
    {
        return $this->message;
    }
}

