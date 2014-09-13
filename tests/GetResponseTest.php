<?php

error_reporting(E_ALL ^ E_NOTICE);

require "vendor/autoload.php";

use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

class GetResponseTest extends PHPUnit_Framework_TestCase
{
    private $stdin;
    private $stderr;
    private $stdout;

    public function setup()
    {
        vfsStream::setup('std');
        $this->stdin = vfsStream::url('std/input');
        $this->stdout = vfsStream::url('std/output');
        $this->stderr = vfsStream::url('std/error');
        
        ClearIce::setStreamUrl('input', $this->stdin);
        ClearIce::setStreamUrl('output', $this->stdout);
        ClearIce::setStreamUrl('error', $this->stderr);        
    }
    
    public function testStreams()
    {
        copy('tests/streams/input.txt', $this->stdin);
        

        
        $this->assertEquals('Hello World', ClearIce::getResponse('Heck'));
        $this->assertStringEqualsFile($this->stdout, 'Heck []: ');
        ClearIce::getResponse('Flag an error', array('answers' => array('value')));
        $this->assertStringEqualsFile($this->stderr, "Please provide a valid answer.\n");
    }
    
    public function testPlainEntry()
    {
        copy('tests/streams/input.txt', $this->stdin);        
        $this->assertEquals('Hello World', ClearIce::getResponse('Say hello world'));
    }
    
    public function testDefault()
    {
        file_put_contents($this->stdin, "\n");
        $this->assertEquals('def', ClearIce::getResponse('Some defaults', array('default' => 'def')));
        $this->assertStringEqualsFile($this->stdout, 'Some defaults [def]: ');        
    }
    
    public function testDefaultOther()
    {
        file_put_contents($this->stdin, "other");
        $this->assertEquals('other', ClearIce::getResponse('Some defaults', array('default' => 'def')));
        $this->assertStringEqualsFile($this->stdout, 'Some defaults [def]: ');        
    }
    
    public function testAnswers()
    {
        file_put_contents($this->stdin, "one");
        $this->assertEquals('one', 
            ClearIce::getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    )
                )
            )
        );
        $this->assertStringEqualsFile($this->stdout, 'Some answers (one/two/three) []: ');         
    }
    
    public function testAnswersDefault()
    {
        file_put_contents($this->stdin, "\n");
        $this->assertEquals('two', 
            ClearIce::getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    ),
                    'default' => 'two'
                )
            )
        );
        $this->assertStringEqualsFile($this->stdout, 'Some answers (one/two/three) [two]: ');          
    }
    
    /*public function testWrongAnswer()
    {
        $cli = $this->getMock('\\clearice\\ClearIce', array('input', 'output', 'error'));
        ClearIce::method('input')->will($this->onConsecutiveCalls("wrong", "one"));
        ClearIce::expects($this->at(0))->method('output')->with('Some answers (one/two/three) [two]: ');
        ClearIce::expects($this->once())->method('error')->with("Please provide a valid answer.\n");
        ClearIce::expects($this->at(3))->method('output')->with('Some answers (one/two/three) [two]: '); 
        
        
        
        $this->assertEquals('one', 
            ClearIce::getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    ),
                    'default' => 'two'
                )
            )
        );
    }
    
    public function testRequired()
    {
        $cli = $this->getMock('\\clearice\\ClearIce', array('input', 'output', 'error'));
        ClearIce::method('input')->will($this->onConsecutiveCalls("", "something"));
        ClearIce::expects($this->at(0))->method('output')->with('Fails first []: ');
        ClearIce::expects($this->once())->method('error')->with("A value is required.\n");
        ClearIce::expects($this->at(3))->method('output')->with('Fails first []: ');          
        $this->assertEquals('something', ClearIce::getResponse('Fails first', array('required' => true)));        
    }
    
    public function testRequiredDefault()
    {
        $cli = $this->getMock('\\clearice\\ClearIce', array('input', 'output'));
        ClearIce::method('input')->will($this->onConsecutiveCalls("", "something"));
        ClearIce::expects($this->at(0))->method('output')->with('Fails first [def]: ');
        $this->assertEquals('def', ClearIce::getResponse('Fails first', array('required' => true, 'default' => 'def')));
    }*/
}
