<?php
/**
 * Created by PhpStorm.
 * User: ekow
 * Date: 6/12/18
 * Time: 5:36 AM
 */

namespace cases;


use clearice\argparser\ArgumentParser;
use PHPUnit\Framework\TestCase;

class ArgumentParserTest extends TestCase
{
    private $argumentParser;

    public function setup()
    {
        $this->argumentParser = new ArgumentParser();

//            array(
//                'short' => 'x',
//                'long' => 'create-default-index',
//                'has_value' => false,
//                "help" => "creates a default index page which lists all the wiki pages in a sorted order"
//            ),
//
//            array(
//                'short' => 'd',
//                'long' => 'some-very-long-option-indeed',
//                'has_value' => false,
//                "help" => "an uneccesarily long option which is meant to to see if the wrapping of help lines actually works."
//            ),
//            array(
//                'short' => 's',
//                'has_value' => false,
//                "help" => "a short option only"
//            ),
//            array(
//                'long' => 'lone-long-option',
//                'has_value' => false,
//                "help" => "a long option only"
//            )
//        ]);
    }

    public function testParse()
    {
        $this->argumentParser->addOption([
            'short_name' => 'i',
            'name' => 'input',
            'type' => 'string',
            'help' => "specifies where the input files for the wiki are found."
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'o',
            'name' => 'output',
            'type' => 'string',
            "help" => "specifies where the wiki should be written to"
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'v',
            'name' => 'verbose',
            "help" => "displays detailed information about everything that happens"
        ]);

        $arguments = $this->argumentParser->parse(["app", "--input", "good"]);
        $this->assertEquals(['input' => 'good'], $arguments);
        $arguments = $this->argumentParser->parse(["app", "--verbose"]);
        $this->assertEquals(['verbose' => true], $arguments);
    }
}