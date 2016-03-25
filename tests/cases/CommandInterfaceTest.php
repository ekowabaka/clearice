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
    
    public function testOptionedCommandInterface()
    {
        require __DIR__ . '/../code/OptionedCommand.php';
        global $argv;
        $argv = ['test', 'optioned', '--input', '/some/path', '--output', '/some/other/path'];
        vfsStream::setup('std');
        ClearIce::reset();
        ClearIce::setStreamUrl('output', vfsStream::url('std/output'));
        ClearIce::addCommands('OptionedCommand');
        ClearIce::parse();
        $this->assertStringEqualsFile(
            vfsStream::url('std/output'), 
            '{"input":"\/some\/path","output":"\/some\/other\/path"}'
        );
    }
    
    public function testStringCommandDeclaration()
    {
        global $argv;
        $argv = ['test', 'mycommand'];
        ClearIce::reset();
        ClearIce::setStreamUrl('output', vfsStream::url('std/output'));
        ClearIce::addCommands('mycommand');
        $options = ClearIce::parse();
        $this->assertEquals('mycommand', $options['__command__']);
    }
}

