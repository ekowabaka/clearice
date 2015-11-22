<?php

namespace clearice;

class Options implements \ArrayAccess, \Iterator
{
    private $options = [];
    private $index = 0;
    
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
    
    public function offsetExists($offset)
    {
        return exists($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->options[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }
    
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

    public function current()
    {
        return $this->options[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->index++;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return isset($this->options[$this->index]);
    }

}

