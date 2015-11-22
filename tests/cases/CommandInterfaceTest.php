<?php
use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

class CommandInterfaceTest extends PHPUnit_Framework_TestCase
{
    public function testCommandInterface()
    {
        require __DIR__ . '/../code/ArbitraryCommand.php';
        
        global $argv;
        $argv = array("test", "arbitrary", "--daemon");        
        
        vfsStream::setup('std');
        ClearIce::reset();
        ClearIce::setStreamUrl('output', vfsStream::url('std/output'));
        ClearIce::addCommands(
            array(
                'command' => 'arbitrary',
                'help' => 'an arbitrary command',
                'class' => 'ArbitraryCommand'
            )
        );        
        ClearIce::addOptions('daemon');
        ClearIce::parse();
        
        $this->assertStringEqualsFile(vfsStream::url('std/output'), '{"daemon":true}');
    }
}

