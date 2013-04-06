<?php
require "ClearICE.php";

class ClearICETest extends PHPUnit_Framework_TestCase
{    
    
    public function setup()
    {
        ClearICE::clearOptions();
        ClearICE::addOptions(
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
            )
        );        
    }
    
    public function testParsing()
    {
        ClearICE::addOptions(            
            's',
            'some-long-option'
        );
        
        global $argv;
                
        $argv = array(
            "test",
            "--input=/myfiles/wiki-sources",
            "--output=/myfiles/wiki",
            "--some-long-option",
            "--verbose"
        );
        
        $options  = ClearICE::parse();
        $this->assertArrayHasKey("input", $options);
        $this->assertArrayHasKey("output", $options);
        $this->assertEquals(
            array(
                "input" => "/myfiles/wiki-sources",
                "output" => "/myfiles/wiki",
                "some-long-option" => true,
                "verbose" => true
            ),
            $options
        );
        
        $argv = array(
            "test",
            "-i/myfiles/wiki-sources",
            "-o/myfiles/wiki",
            "-v"
        );
        
        $options  = ClearICE::parse();
        $this->assertArrayHasKey("input", $options);
        $this->assertArrayHasKey("output", $options);
        $this->assertEquals(
            array(
                "input" => "/myfiles/wiki-sources",
                "output" => "/myfiles/wiki",
                "verbose" => true
            ),
            $options
        );
        
        $argv = array(
            "test",
            "--input=/myfiles/wiki-sources",
            "-o/myfiles/wiki",
        );
        
        $options  = ClearICE::parse();
        $this->assertArrayHasKey("input", $options);
        $this->assertArrayHasKey("output", $options);
        $this->assertEquals(
            array(
                "input" => "/myfiles/wiki-sources",
                "output" => "/myfiles/wiki"
            ),
            $options
        );
        
        
        $argv = array(
            "test",
            "-vxs",
            "stand_alone"
        );        
        $options  = ClearICE::parse();
        
        $this->assertArrayHasKey("verbose", $options);
        $this->assertArrayHasKey("create-default-index", $options);
        
        $argv = array(
            "test",
            "-sxv",
            "stand_alone"
        );        
        $options  = ClearICE::parse();
        
        $this->assertEquals(
            array(
                'create-default-index' => true,
                'verbose' => true,
                's' => true,
                'stand_alones' => array(
                    'stand_alone'
                )
            ),
            $options
        );
        
        
        $argv = array(
            "test",
            "--input=/myfiles/wiki-sources",
            "-o/myfiles/wiki",
            "stand_alone_1",
            "stand_alone_2"
        );        
        $options  = ClearICE::parse();
        $this->assertArrayHasKey("stand_alones", $options);
        $this->assertEquals(
            array(
                "input" => "/myfiles/wiki-sources",
                "output" => "/myfiles/wiki",
                "stand_alones" => array(
                    "stand_alone_1",
                    "stand_alone_2",
                )
            ),
            $options
        );    
        
        $argv = array(
            "test",
            "-ug",
            "--unknown-option",
            "--another-unknown=something"
        );
        $options = ClearICE::parse();
        $this->assertArrayHasKey("unknowns", $options);
        $this->assertEquals(
            array(
                "unknowns" => array(
                    "u",
                    "g",
                    "unknown-option",
                    "another-unknown"
                ),
                'unknown-option' => true,
                'another-unknown' => "something",
                'u' => true,
                'g' => true
            ),
            $options
        );            
    }
    
    public function testHelp()
    {
        global $argv;
        $argv = array(
            "test.php"
        );
        
        ClearICE::setUsage("[input] [options]..");
        ClearICE::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearICE. This app practically does nothing.");
        ClearICE::setFootnote("Hope you had a nice time learning about ClearICE. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearICE::addHelp();
        
        $helpMessage = ClearICE::getHelpMessage();
        
        $this->assertEquals(
"Simple Wiki version 1.0
A sample or should I say dummy wiki app to help explain ClearICE. This app
practically does nothing.

Usage:
  test.php [input] [options]..

  -i,  --input=VALUE         specifies where the input files for the wiki are
                             found.
  -o,  --output=VALUE        specifies where the wiki should be written to
  -v,  --verbose             displays detailed information about everything
                             that happens
  -x,  --create-default-index 
                             creates a default index page which lists all the
                             wiki pages in a sorted order
  -h,  --help                shows this help message

Hope you had a nice time learning about ClearICE. We're pretty sure your
cli apps would no longer be boring to work with.

Report bugs to bugs@clearice.tld
", $helpMessage);
        
        
        ClearICE::setUsage(array("[input] [options]..", "[input] [something] [options].."));
        ClearICE::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearICE. This app practically does nothing.");
        ClearICE::setFootnote("Hope you had a nice time learning about ClearICE. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearICE::addHelp();
        
        $helpMessage = ClearICE::getHelpMessage();
        
        $this->assertEquals(
"Simple Wiki version 1.0
A sample or should I say dummy wiki app to help explain ClearICE. This app
practically does nothing.

Usage:
  test.php [input] [options]..
  test.php [input] [something] [options]..

  -i,  --input=VALUE         specifies where the input files for the wiki are
                             found.
  -o,  --output=VALUE        specifies where the wiki should be written to
  -v,  --verbose             displays detailed information about everything
                             that happens
  -x,  --create-default-index 
                             creates a default index page which lists all the
                             wiki pages in a sorted order
  -h,  --help                shows this help message

Hope you had a nice time learning about ClearICE. We're pretty sure your
cli apps would no longer be boring to work with.

Report bugs to bugs@clearice.tld
", $helpMessage);
        
    }
}