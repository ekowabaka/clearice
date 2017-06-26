<?php

namespace clearice;

class Options
{
    private $options = [];
    
    /**
     * A map of all the options the parser recognises. 
     * The map is actually an array which associates short or long options with 
     * their appropriate parameters. Options which have both long and short 
     * versions would be repeated. This structure is used to quickly find the 
     * paramters of an option whether in the short form or long form. This 
     * parameter is automatically populated by the library as options are added.
     * 
     * @var array
     */    
    private $map;
    
    public function add($options)
    {
        foreach ($options as $option) {
            if (is_string($option)) {
                $option = $this->stringOptionToArray($option);
            }
            $option = $this->fillOption($option);
            $this->options[] = $option;
            $command = isset($option['command']) ? $option['command'] : '__default__';
            if (isset($option['short'])) {
                $this->map[$command][$option['short']] = $option;
            }
            if (isset($option['long'])) {
                $this->map[$command][$option['long']] = $option;
            }
        }        
        $this->options += $options;
    }

    private function fillOption($option)
    {
        $option['has_value'] = isset($option['has_value']) ? $option['has_value'] : false;
        $option['command'] = isset($option['command']) ? $option['command'] : null;
        $option['multi'] = isset($option['multi']) ? $option['multi'] : null;
        $option['group'] = isset($option['group']) ? $option['group'] : null;
        return $option;
    }
    
    public function getMap()
    {
        return $this->map;
    }
    
    private function stringOptionToArray($option)
    {
        $newOption = [];
        if (strlen($option) == 1) {
            $newOption['short'] = $option;
        } else {
            $newOption['long'] = $option;
        }
        return $newOption;
    }
    
    public function getArray()
    {
        return $this->options;
    }
    
    /**
     * Return the default values that are specified for certain options.
     * @return array
     */
    public function getDefaults($command)
    {
        $defaults = [];
        $command = $command == '__default__' ? NULL : $command;
        foreach($this->options as $option) {
            if(array_key_exists('default', $option) && $option['command'] == $command) {
                $defaults[$option['long']] = $option['default'];
            }
        }
        return $defaults;
    }
}
 
