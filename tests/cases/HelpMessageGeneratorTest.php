<?php

namespace cases;


use clearice\argparser\HelpMessageGenerator;
use PHPUnit\Framework\TestCase;

class HelpMessageGeneratorTest extends TestCase
{
    /**
     * @var HelpMessageGenerator
     */
    private $helpMessageGenerator;

    public function setUp()
    {
        $this->helpMessageGenerator = new HelpMessageGenerator();
    }

    public function test()
    {
        $desciption = "Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.";
        $footer = "Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld";
        $options = [
            array(
                'short_name' => 'i',
                'name' => 'input',
                'type' => 'string',
                'help' => "specifies where the input files for the wiki are found."
            ),
            array(
                'short_name' => 'o',
                'name' => 'output',
                'type' => 'string',
                "help" => "specifies where the wiki should be written to"
            ),
            array(
                'short_name' => 'v',
                'name' => 'verbose',
                "help" => "displays detailed information about everything that happens"
            ),
            array(
                'short_name' => 'x',
                'name' => 'create-default-index',
                "help" => "creates a default index page which lists all the wiki pages in a sorted order"
            ),

            array(
                'short_name' => 'd',
                'name' => 'some-very-long-option-indeed',
                "help" => "an uneccesarily long option which is meant to to see if the wrapping of help lines actually works."
            ),
            array(
                'short_name' => 's',
                "help" => "a short option only"
            ),
            array(
                'name' => 'lone-long-option',
                "help" => "a long option only"
            )
        ];
        
        $message = $this->helpMessageGenerator->generate('/path/to/test.php', null, $options, $desciption, $footer);
        $this->assertEquals(file_get_contents('tests/data/help-message.txt'), $message);
    }
}