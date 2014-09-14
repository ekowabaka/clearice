<?php
namespace clearice\parsers;

class LongOptionParser extends BaseParser
{
    /**
     * @param string $argument
     * @param mixed $value
     * @param string $command Description
     */
    public function parse($argument, $value, $command)
    {
        $return = true;
        if(!isset($this->optionsMap[$command][$argument]))
        {
            if(self::$logUnknowns) 
            {
                $this->parser->addUnknownOption($argument);
                $this->parser->addParsedOption($argument, $value);
            }
            $return = false;
        }
        else
        {
            $this->setValue($command, $argument, $value);
        }
        return $return;
    }}
