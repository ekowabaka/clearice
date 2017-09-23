<?php

use PHPUnit\Framework\TestCase;
use clearice\ArgumentParser;
use clearice\ConsoleIO;

class DefaultValuesTest extends TestCase
{
    private $argumentParser;
    
    public function setUp()
    {
        $io = new ConsoleIO();
        $this->argumentParser = new ArgumentParser($io);
        $this->argumentParser->addOptions([
            [
                'long' => 'has-default',
                'default' => 'def',
                'has_value' => true
            ],
            [
                'long' => 'another-default',
                'default' => 'def2',
                'has_value' => true
            ],
            [
                'long' => 'no-default'
            ]
        ]);
    }
        
    public function testDefaultValues()
    {
        $values = $this->argumentParser->parse(array(
            "test"
        ));
        $this->assertEquals(
            ['has-default' => 'def', 'another-default' => 'def2'],
            $values
        );
    }
}
