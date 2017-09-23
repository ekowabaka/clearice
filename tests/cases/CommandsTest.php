<?php

use clearice\ConsoleIO;
use PHPUnit\Framework\TestCase;
use clearice\ArgumentParser;

class CommandsTest extends TestCase
{   
    
    private $argumentParser;
    
    public function setup()
    {
        $io = new ConsoleIO();
        $this->argumentParser = new ArgumentParser($io);
        $this->argumentParser->addCommands(
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
    
    public function testCommands()
    {
        global $argv;
        $argv = array(
            "test",
            "init",
            "--directory=./",
            '-v',
            '--export'
        );
        
        $options = $this->argumentParser->parse();
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
    
    public function testDefaultCommand()
    {
        global $argv;
        $argv = array(
            "test",
            '-v'
        );
        
        $options = $this->argumentParser->parse();
        $this->assertEquals(
            array(
                'verbose' => true,
            ),
            $options
        );
    }    
}
