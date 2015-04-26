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

class BaseParser
{
    /**
     *
     * @var \clearice\ArgumentParser
     */
    protected $parser;
    protected $optionsMap;
    protected $parsedOptions;
    protected static $logUnknowns;
    protected $unknownOptions;
    
    public function __construct($parser, $optionsMap)
    {
        $this->parser = $parser;
        $this->optionsMap = $optionsMap;
    }
    
    public static function setLogUnknowns($logUnknowns)
    {
        self::$logUnknowns = $logUnknowns;
    }
    
    protected function setValue($command, $key, $value)
    {
        if($this->optionsMap[$command][$key]['multi'] === true)
        {
            $this->parser->addParsedMultiOption($key, $value);
        }
        else
        {
            $this->parser->addParsedOption($key, $value);
        }        
    }
    
    protected function parseStandAloneValue($argument, $value, $command)
    {
        $newValue = $value;
        if($this->optionsMap[$command][$argument]['has_value'] == true && $value === true)
        {
            $lookahead = $this->parser->getLookAheadArgument();
            if(!preg_match('/^(--|-)(.*)/', $lookahead))
            {
                $this->parser->moveToNextArgument();
                $newValue = $lookahead;
            }
        }
        return $newValue;
    }    
}

