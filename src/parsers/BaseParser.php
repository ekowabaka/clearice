<?php
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
}

