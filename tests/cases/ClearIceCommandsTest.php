<?php

error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";

use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

class ClearIceCommandsTest extends PHPUnit_Framework_TestCase
{   
    public function setup()
    {
        ClearIce::reset();
        ClearIce::addCommands(
            array(
                'command' => 'init',
                'help' => 'initialize a directory with the source files of the wiki'
            ), 
            array(
                'command' => 'generate',
                'help' => 'generate the data for a given directory and store it somewhere cool',
                'usage' => 'generate --target=[TARGET]'
            ), 
            'export'
        );
        ClearIce::addOptions(
            array(
                'command' => 'init',
                'short' => 'd',
                'long' => 'directory',
                'has_value' => true,
                'help' => "specify the directory to be initialized"
            ),
            array(
                'command' => 'init',
                'short' => 't',
                'long' => 'title',
                'has_value' => true,
                'help' => "title of the new wiki to be initialized"
            ),                
            array(
                'command' => 'generate',
                'short' => 't',
                'long' => 'target',
                'has_value' => true,
                "help" => "specify where the generated wiki should be"
            ),
            array(
                'command' => 'export',
                'short' => 'f',
                'long' => 'format',
                "help" => "specify the format of the exported wiki"
            ),
            array(
                'short' => 'v',
                'long' => 'verbose',
                'has_value' => false,
                "help" => "display more information"
            )                              
        );        
    }
    
    function testCommands()
    {
        global $argv;
        $argv = array(
            "test",
            "init",
            "--directory=./",
            '-v',
            '--export'
        );
        
        $options = ClearIce::parse();
        $this->assertEquals(
            array(
                '__command__' => 'init',
                'directory' => './',
                'verbose' => true,
                'export' => true,
                'unknowns' => array(
                    'export'
                )
            ),
            $options
        );
    }
    
    function testDefaultCommand()
    {
        global $argv;
        $argv = array(
            "test",
            '-v'
        );
        
        $options = ClearIce::parse();
        $this->assertEquals(
            array(
                'verbose' => true,
            ),
            $options
        );
    }
    
    function testCommandHelp()
    {
        global $argv;
        $argv = array(
            'test',
            '--help'
        );
                
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        ClearIce::setStreamUrl('output', $stdout);
        
        ClearIce::setUsage("[input] [options]..");
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();
        ClearIce::parse();
        
        $this->assertFileEquals('tests/data/help_commands.txt', vfsStream::url('std/output'));
    }
    
    function testHelpForCommand()    
    {
        global $argv;
        $argv = array(
            'test',
            'init',
            '--help'
        );         
        
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        ClearIce::setStreamUrl('output', $stdout);

        ClearIce::setUsage("[input] [options]..");
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();        
        ClearIce::parse();
        $this->assertFileEquals('tests/data/help_for_command.txt', vfsStream::url('std/output'));
    }
    
    function testHelpCommand()    
    {
        global $argv;
        $argv = array(
            'test',
            'help',
            'init'
        );         
        
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        ClearIce::setStreamUrl('output', $stdout);

        ClearIce::setUsage("[input] [options]..");
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();        
        ClearIce::parse();
        $this->assertFileEquals('tests/data/help_for_command.txt', vfsStream::url('std/output'));
    }    
    
    function testHelpCommandUsage()    
    {
        global $argv;
        $argv = array(
            'test',
            'help',
            'generate'
        );         
        
        vfsStream::setup('std');
        $stdout = vfsStream::url('std/output');        
        ClearIce::setStreamUrl('output', $stdout);

        ClearIce::setUsage("[input] [options]..");
        ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        ClearIce::addHelp();        
        ClearIce::parse();
        $this->assertFileEquals('tests/data/help_for_command_usage.txt', vfsStream::url('std/output'));
    }
}
