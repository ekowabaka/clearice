<?php

class DefaultValuesTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        \clearice\ClearIce::addOptions(
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
        );
    }
    
    public function tearDown()
    {
        parent::tearDown();
        \clearice\ClearIce::reset();
    }
    
    public function testDefaultValues()
    {
        global $argv;
        
        $argv = array(
            "test"
        );        
        
        $values = \clearice\ClearIce::parse();
        $this->assertEquals(
            ['has-default' => 'def', 'another-default' => 'def2'],
            $values
        );
    }
}
