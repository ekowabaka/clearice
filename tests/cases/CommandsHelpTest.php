<?php

use clearice\ArgumentParser;
use clearice\ConsoleIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CommandsHelpTest extends TestCase
{
    private $argumentParser;
    
    public function setup()
    {
        vfsStream::setup('std');        
        
        $io = new ConsoleIO();
        $io->setStreamUrl('output', vfsStream::url('std/output'));
                
        $this->argumentParser = new ArgumentParser($io);
        $this->argumentParser->addCommands([
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
        ]);
        $this->argumentParser->addOptions([
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
        ]);        
    }
    
    public function testCommandHelp()
    {
                
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp('test');
        $this->argumentParser->parse(array(
            'test',
            '--help'
        ));
        
        $this->assertFileEquals('tests/data/help_commands.txt', vfsStream::url('std/output'));
    }
    
    public function testHelpForCommand()    
    {    
        
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp('test');        
        $this->argumentParser->parse(array(
            'test',
            'init',
            '--help'
        ));
        $this->assertFileEquals('tests/data/help_for_command.txt', vfsStream::url('std/output'));
    }
    
    public function testHelpCommand()    
    {
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp();        
        $this->argumentParser->parse(array(
            'test',
            'help',
            'init'
        ));
        $this->assertFileEquals('tests/data/help_for_command.txt', vfsStream::url('std/output'));
    }    
    
    public function testHelpCommandUsage()    
    {              
        $this->argumentParser->setUsage("[input] [options]..");
        $this->argumentParser->setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
        $this->argumentParser->setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
        $this->argumentParser->addHelp();        
        $this->argumentParser->parse(array(
            'test',
            'help',
            'generate'
        ));
        $this->assertFileEquals('tests/data/help_for_command_usage.txt', vfsStream::url('std/output'));
    }
}
