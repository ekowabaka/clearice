<?php
require "ClearIce.php";

error_reporting(E_ALL ^ E_NOTICE);

class ClearIceTest extends PHPUnit_Framework_TestCase
{    
    
    public function setup()
    {
        ClearIce::clearOptions();
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
    
    public function testParsing()
    {
        ClearIce::addOptions(            
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
        
        $options  = ClearIce::parse();
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
        
        $options  = ClearIce::parse();
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
        
        $options  = ClearIce::parse();
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
        $options  = ClearIce::parse();
        
        $this->assertArrayHasKey("verbose", $options);
        $this->assertArrayHasKey("create-default-index", $options);
        
        $argv = array(
            "test",
            "-sxv",
            "stand_alone"
        );        
        $options  = ClearIce::parse();
        
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
        $options  = ClearIce::parse();
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
        $options = ClearIce::parse();        
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
        
        ClearIce::setUsage("[input] [options]..");
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();
        
        $helpMessage = ClearIce::getHelpMessage();
        
        print $helpMessage;
        
        $this->assertEquals(
"Simple Wiki version 1.0
A sample or should I say dummy wiki app to help explain ClearIce. This app
practically does nothing.

Usage:
  test.php [input] [options]..

  -i, --input=VALUE          specifies where the input files for the wiki are
                             found.
  -o, --output=VALUE         specifies where the wiki should be written to
  -v, --verbose              displays detailed information about everything
                             that happens
  -x, --create-default-index creates a default index page which lists all the
                             wiki pages in a sorted order
  -d, --some-very-long-option-indeed 
                             an uneccesarily long option which is meant to to
                             see if the wrapping of help lines actually works.
  -s                         a short option only
  --lone-long-option         a long option only
  -h, --help                 shows this help message

Hope you had a nice time learning about ClearIce. We're pretty sure your
cli apps would no longer be boring to work with.

Report bugs to bugs@clearice.tld
", $helpMessage);
        
    }
}