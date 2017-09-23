<?php

use clearice\ArgumentParser;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use clearice\ConsoleIO;

class HelpTest extends TestCase
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
    
    public function testHelp()
    {   
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp();
        
        $helpMessage = $this->argumentParser->getHelpMessage("test.php");
        $this->assertEquals(file_get_contents('tests/data/help.txt'), $helpMessage);
        
    }
    
    public function testHelpOption()
    {
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->getIO()->setStreamUrl('output', $stdout);
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp();
        $this->argumentParser->parse(array(
            "test.php",
            "--help"
        ));
        
        $this->assertFileEquals('tests/data/help.txt', vfsStream::url('std/output'));
    }
    
    public function testStrict()
    {
        vfsStream::setup('std');
        $stderr = vfsStream::url('std/error');
        
        $this->argumentParser->addOptions(['a-known']);
        $this->argumentParser->setStrict(true);
        $this->argumentParser->getIO()->setStreamUrl('error', $stderr);
        $this->argumentParser->parse(array(
            "test.php",
            '--an-unknown'
        )); 
        
        $this->assertStringEqualsFile(
            $stderr, 
            "test.php: invalid option -- an-unknown\n"
        );
    }
    
    public function testStrictWithHelp()
    {
        vfsStream::setup('std');
        $stderr = vfsStream::url('std/error');
        
        $this->argumentParser->addOptions(['a-known']);
        $this->argumentParser->setStrict(true);
        $this->argumentParser->getIO()->setStreamUrl('error', $stderr);
        $this->argumentParser->addHelp();
        $this->argumentParser->parse(array(
            "test.php",
            '--an-unknown'
        ));
                
        $this->assertStringEqualsFile(
            $stderr, 
            "test.php: invalid option -- an-unknown\nTry `test.php --help` for more information\n"
        );
    }
}
