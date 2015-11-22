<?php

use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

define('TESTING', 'yes');

class ClearIceTest extends PHPUnit_Framework_TestCase
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
    
    public function testParsingLongOptions()
    {
        global $argv;
        ClearIce::addOptions(            
            's',
            'some-long-option'
        );        
                
        $argv = array(
            "test",
            "--input=/myfiles/wiki-sources",
            "--output=/myfiles/wiki",
            "--some-long-option",
            "--verbose"
        );
        
        $options = ClearIce::parse();
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
    }
    
    public function testParsingShortOptions()
    {   
        global $argv;
        
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
    }
    
    public function testParsingMixedOptions()
    {
        global $argv;
        
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
    }
        
    public function testParsingGroupedShortsAndStandAlone()
    {
        global $argv;
        $argv = array(
            "test",
            "-vxs",
            "stand_alone"
        );        
        $options  = ClearIce::parse();
        
        $this->assertArrayHasKey("verbose", $options);
        $this->assertArrayHasKey("create-default-index", $options);
    }
    
    public function testParsingGroupedShortsAndStandAloneReversed()
    {
        global $argv;
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
    }
        
    public function testParsingMixedAndStandAlones()
    {
        global $argv;
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
    }
    
    public function testParsingUnknowns()
    {
        global $argv;
        
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
        $this->assertEquals(file_get_contents('tests/data/help.txt'), $helpMessage);
        
    }
    
    public function testHelpOption()
    {
        global $argv;
        $argv = array(
            "test.php",
            "--help"
        );  
        
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        
        ClearIce::setUsage("[input] [options]..");
        ClearIce::setStreamUrl('output', $stdout);
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();
        ClearIce::parse();
        
        $this->assertFileEquals('tests/data/help.txt', vfsStream::url('std/output'));
    }
    
    public function testStrict()
    {
        global $argv;
        $argv = array(
            "test.php",
            '--an-unknown'
        );
        
        vfsStream::setup('std');
        $stderr = vfsStream::url('std/error');
        
        ClearIce::addOptions('a-known');
        ClearIce::setStrict(true);
        ClearIce::setStreamUrl('error', $stderr);
        ClearIce::parse(); 
        
        $this->assertStringEqualsFile(
            $stderr, 
            "test.php: invalid option -- an-unknown\n"
        );
    }
    
    public function testStrictWithHelp()
    {
        global $argv;
        $argv = array(
            "test.php",
            '--an-unknown'
        );

        vfsStream::setup('std');
        $stderr = vfsStream::url('std/error');
        
        ClearIce::addOptions('a-known');
        ClearIce::setStrict(true);
        ClearIce::setStreamUrl('error', $stderr);
        ClearIce::addHelp();
        ClearIce::parse();
                
        $this->assertStringEqualsFile(
            $stderr, 
            "test.php: invalid option -- an-unknown\nTry `test.php --help` for more information\n"
        );
    }
    
    public function testMultipleUsage()
    {
        global $argv;
        $argv = array(
            "test.php"
        );
        
        ClearIce::setUsage(
            array(
                "[input] [options]..",
                "[output] [options].."
            )
        );
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();
        
        $helpMessage = ClearIce::getHelpMessage();
        
        $this->assertEquals(file_get_contents('tests/data/help_multi_usage.txt'), $helpMessage);
    }
    
    public function testMultiOptions()
    {
        global $argv;
        $argv = array(
            "test.php",
            "--some-multi-option=one",
            "--some-multi-option=two",
            "-mthree"
        );
        
        ClearIce::addOptions(array(
            'long' => 'some-multi-option',
            'short' => 'm',
            'multi' => true,
            'has_value' => true
        ));
        
        $this->assertEquals(
            array(
                'some-multi-option' => array(
                    'one', 'two', 'three'
                )
            ),
            ClearIce::parse()
        );
    }
}
