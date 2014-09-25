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