<?php
use clearice\ClearIce;

class OptionGroupsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        ClearIce::reset();
    }
    
    public function testOptionGroups(){
        ClearIce::addGroups(
            ['group' => 'location', 'help' => 'Location options'],
            ['group' => 'testing', 'help' => 'Some testing options']
        );
        ClearIce::addOptions(
            array(
                'short' => 'i',
                'long' => 'input',
                'has_value' => true,
                'group' => 'location',
                'help' => "specifies where the input files for the wiki are found."
            ),
            array(
                'short' => 'o',
                'long' => 'output',
                'has_value' => true,
                'group' => 'location',
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
                'group' => 'testing',
                "help" => "an uneccesarily long option which is meant to to see if the wrapping of help lines actually works."
            ),
            array(
                'short' => 's',
                'has_value' => false,
                'group' => 'testing',
                "help" => "a short option only"
            ),
            array(
                'long' => 'lone-long-option',
                'has_value' => false,
                'group' => 'testing',
                "help" => "a long option only"
            )
        );        
        
        $helpMessage = ClearIce::getHelpMessage();
        $this->assertEquals(file_get_contents('tests/data/help_groups.txt'), $helpMessage);
    }
}
