<?php
namespace clearice\parsers;

class ShortOptionParser extends BaseParser
{
    private $skippedShorts;    
    
    private function getShortOptionLongKey($command, $shortOption)
    {
        return isset($this->optionsMap[$command][$shortOption]['long']) ? 
            $this->optionsMap[$command][$shortOption]['long'] : $shortOption;        
    }
    
    /**
     * @param string $shortOptionsString
     */
    private function parseShortOptions($shortOptionsString, $command)
    {
        if(strlen($shortOptionsString) == 0) return;
        $shortOption = $shortOptionsString[0];
        $remainder = substr($shortOptionsString, 1);
            
        if(isset($this->optionsMap[$command][$shortOption]))
        {
            $key = $this->getShortOptionLongKey($command, $shortOption);
            if($this->optionsMap[$command][$shortOption]['has_value'] === true)
            {
                $this->setValue($command, $key, $remainder);
            }
            else
            {
                //$this->parsedOptions[$key] = true;
                $this->parser->addParsedOption($key, true);
                $this->parseShortOptions($remainder, $command);
            }
        }
        else
        {
            if(self::$logUnknowns) 
            {
                //$this->unknownOptions[] = $shortOption;
                //$this->parsedOptions[$shortOption] = true;
                $this->parser->addUnknownOption($shortOption);
                $this->parser->addParsedOption($shortOption, true);
            }
            
            $this->skippedShorts .= $shortOption;
            $this->parseShortOptions($remainder, $command);
        }
    }
    
    public function parse($argument, $command)
    {
        $this->skippedShorts = '';
        $this->parseShortOptions($argument, $command);
        if($this->skippedShorts != '' && $command != '__default__')
        {
            self::$logUnknowns = true;                
            $this->parseShortOptions($this->skippedShorts, '__default__');
        }
    }    
}