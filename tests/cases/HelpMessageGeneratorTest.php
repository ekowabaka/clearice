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
    private $desciption = "Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.";
    private $footer = "Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld";

    public function setUp()
    {
        $this->helpMessageGenerator = new HelpMessageGenerator();
    }

    public function testDefault()
    {
        $options = [
            array(
                'short_name' => 'i',
                'name' => 'input',
                'type' => 'string',
                'help' => "specifies where the input files for the wiki are found.",
                'command' => ''
            ),
            array(
                'short_name' => 'o',
                'name' => 'output',
                'type' => 'string',
                "help" => "specifies where the wiki should be written to",
                'command' => ''
            ),
            array(
                'short_name' => 'v',
                'name' => 'verbose',
                "help" => "displays detailed information about everything that happens",
                'command' => ''
            ),
            array(
                'short_name' => 'x',
                'name' => 'create-default-index',
                "help" => "creates a default index page which lists all the wiki pages in a sorted order",
                'command' => ''
            ),

            array(
                'short_name' => 'd',
                'name' => 'some-very-long-option-indeed',
                "help" => "an uneccesarily long option which is meant to to see if the wrapping of help lines actually works.",
                'command' => ''
            ),
            array(
                'short_name' => 's',
                "help" => "a short option only",
                'command' => ''
            ),
            array(
                'name' => 'lone-long-option',
                "help" => "a long option only",
                'command' => ''
            )
        ];

        $message = $this->helpMessageGenerator->generate('/path/to/test.php', null, $options, $this->desciption, $this->footer);
        $this->assertEquals(file_get_contents('tests/data/help-message.txt'), $message);
    }

    public function testCommand()
    {
        $commands = [
            [
                'name' => 'init',
                'help' => 'initialize a directory with the source files of the wiki'
            ],
            [
                'name' => 'generate',
                'help' => 'generate the data for a given directory and store it somewhere cool',
            ],
            [
                'name' => 'export',
                'help' => 'export wiki to a different format',
            ]
        ];
        $options = [
            [
                'command' => 'init',
                'short_name' => 'd',
                'name' => 'directory',
                'type' => 'string',
                'help' => "specify the directory to be initialized"
            ],
            [
                'command' => 'init',
                'short_name' => 't',
                'name' => 'title',
                'type' => 'string',
                'help' => "title of the new wiki to be initialized"
            ],
            [
                'command' => 'generate',
                'short_name' => 't',
                'name' => 'target',
                'type' => 'string',
                "help" => "specify where the generated wiki should be"
            ],
            [
                'command' => 'export',
                'short_name' => 'f',
                'name' => 'format',
                "help" => "specify the format of the exported wiki"
            ],
            [
                'command' => '',
                'short_name' => 'v',
                'name' => 'verbose',
                "help" => "display more information"
            ],
            [
                'command' => '',
                'short_name' => 'h',
                'name' => 'help',
                "help" => "Shows this help message"
            ]
        ];
        $message = $this->helpMessageGenerator->generate("app", "init", $options, $this->desciption, $this->footer);
        $this->assertEquals(file_get_contents('tests/data/help-init-message.txt'), $message);
    }
}