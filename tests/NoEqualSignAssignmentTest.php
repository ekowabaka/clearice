<?php
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";

use clearice\ClearIce;

class NoEqualSignAssignmentTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        ClearIce::reset();
        ClearIce::addOptions(
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
        );        
    }
    
    public function testNoEqualsAssignment()
    {
        global $argv;
        $argv = array(
            'wiki',
            '--output',
            '/var/www/wiki',
            '--input',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );        
    }

    public function testShortNoEqualsAssignment()
    {
        global $argv;
        $argv = array(
            'wiki',
            '-o',
            '/var/www/wiki',
            '-i',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );
    }        
    
    public function testMixedAssignment()
    {
        global $argv;
        $argv = array(
            'wiki',
            '--output',
            '/var/www/wiki',
            '-i',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );
    }     
    
    public function testMixedAssignment2()
    {
        global $argv;
        $argv = array(
            'wiki',
            '--output=/var/www/wiki',
            '-i',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => '/var/www/wiki',
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );
    }        
    
    public function testSkipping()
    {
        global $argv;
        $argv = array(
            'wiki',
            '--output',
            '--input',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => true,
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );
    }         
    
    public function testSkipping2()
    {
        global $argv;
        $argv = array(
            'wiki',
            '--output',
            '-i',
            '/var/input'
        );
        
        $this->assertEquals(
            array(
                'output' => true,
                'input' => '/var/input'
            ),
            ClearIce::parse()
        );
    }         
}
