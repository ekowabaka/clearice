<?php

use clearice\ArgumentParser;
use PHPUnit\Framework\TestCase;
use clearice\ConsoleIO;

class ClearIceTest extends TestCase
{
    
    private $argumentParser;
    
    public function setup()
    {
        $io = new ConsoleIO();
        $this->argumentParser = new ArgumentParser($io);
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
    
    public function testParsingLongOptions()
    {
        $this->argumentParser->addOptions([         
            's',
            'some-long-option'
        ]);        
        
        $options = $this->argumentParser->parse(array(
            "test",
            "--input=/myfiles/wiki-sources",
            "--output=/myfiles/wiki",
            "--some-long-option",
            "--verbose"
        ));
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
        
        $options  = $this->argumentParser->parse(array(
            "test",
            "-i/myfiles/wiki-sources",
            "-o/myfiles/wiki",
            "-v"
        ));
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
        $options  = $this->argumentParser->parse(array(
            "test",
            "--input=/myfiles/wiki-sources",
            "-o/myfiles/wiki",
        ));
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
        $options  = $this->argumentParser->parse(array(
            "test",
            "-vxs",
            "stand_alone"
        ));
        
        $this->assertArrayHasKey("verbose", $options);
        $this->assertArrayHasKey("create-default-index", $options);
    }
    
    public function testParsingGroupedShortsAndStandAloneReversed()
    {        
        $options  = $this->argumentParser->parse(array(
            "test",
            "-sxv",
            "stand_alone"
        ));
        
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
        $options  = $this->argumentParser->parse(array(
            "test",
            "--input=/myfiles/wiki-sources",
            "-o/myfiles/wiki",
            "stand_alone_1",
            "stand_alone_2"
        ));
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
        $options = $this->argumentParser->parse(array(
            "test",
            "-ug",
            "--unknown-option",
            "--another-unknown=something"
        ));        
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
    
    public function testMultipleUsage()
    {   
        $this->argumentParser->setUsage(
            array(
                "[input] [options]..",
                "[output] [options].."
            )
        );
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp();
        
        $helpMessage = $this->argumentParser->getHelpMessage('test.php');
        
        $this->assertEquals(file_get_contents('tests/data/help_multi_usage.txt'), $helpMessage);
    }
    
    public function testMultiOptions()
    {
        
        $this->argumentParser->addOptions([array(
            'long' => 'some-multi-option',
            'short' => 'm',
            'multi' => true,
            'has_value' => true
        )]);
        
        $this->assertEquals(
            array(
                'some-multi-option' => array(
                    'one', 'two', 'three'
                )
            ),
            $this->argumentParser->parse(array(
            "test.php",
            "--some-multi-option=one",
            "--some-multi-option=two",
            "-mthree"
        ))
        );
    }
}
