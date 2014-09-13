<?php

error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";

use clearice\ClearIce;

class ClearIceCommandsTest extends PHPUnit_Framework_TestCase
{   
    public function setup()
    {
        ClearIce::addCommands('init', 'generate', 'export');
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
            "--directory=./"
        );
        
        $options = ClearIce::parse();
        $this->assertEquals(
            array(
                '__command__' => 'init',
                'directory' => './'
            ),
            $options
        );
    }
}