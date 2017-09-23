<?php

use clearice\ArgumentParser;
use clearice\ConsoleIO;
use PHPUnit\Framework\TestCase;

class NoEqualSignAssignmentTest extends TestCase
{
    private $argumentParser;
    
    public function setup()
    {
        $this->argumentParser = new ArgumentParser(new ConsoleIO());
        $this->argumentParser->addOptions([
            array(
                'short' => 'i',
                'long' => 'input',
                'has_value' => true,
                'help' => "specifies where the input files for the wiki are found."
            ),
            array(
                'short' => 'o',
                'long' => 'output',
                'has_value' => true,
                "help" => "specifies where the wiki should be written to"
            ),
            array(
                'short' => 'v',
                'long' => 'verbose',
                "help" => "displays detailed information about everything that happens"
            ),
            array(
                'short' => 'x',
                'long' => 'create-default-index',
                'has_value' => false,
                "help" => "creates a default index page which lists all the wiki pages in a sorted order"
            ),
            
            array(
                'short' => 'd',
                'long' => 'some-very-long-option-indeed',
                'has_value' => false,
                "help" => "an uneccesarily long option which is meant to to see if the wrapping of help lines actually works."
            ),
            array(
                'short' => 's',
                'has_value' => false,
                "help" => "a short option only"
            ),
            array(
                'long' => 'lone-long-option',
                'has_value' => false,
                "help" => "a long option only"
            )
        ]);        
    }
    
    public function testNoEqualsAssignment()
    {
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '--output',
                '/var/www/wiki',
                '--input',
                '/var/input'
            ))
        );        
    }

    public function testShortNoEqualsAssignment()
    {
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '-o',
                '/var/www/wiki',
                '-i',
                '/var/input'
            ))
        );
    }        
    
    public function testMixedAssignment()
    {
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '--output',
                '/var/www/wiki',
                '-i',
                '/var/input'
            ))
        );
    }     
    
    public function testMixedAssignment2()
    {
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '--output=/var/www/wiki',
                '-i',
                '/var/input'
            ))
        );
    }        
    
    public function testSkipping()
    {
        $this->assertEquals(
            array(
                'output' => true,
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '--output',
                '--input',
                '/var/input'
            ))
        );
    }         
    
    public function testSkipping2()
    {
        $this->assertEquals(
            array(
                'output' => true,
                'input' => '/var/input'
            ),
            $this->argumentParser->parse(array(
                'wiki',
                '--output',
                '-i',
                '/var/input'
            ))
        );
    }         
}
