<?php
require_once "ClearIce.php";

error_reporting(E_ALL ^ E_NOTICE);

class GetResponseTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        if(file_exists('tests/streams/output.txt'))
        {
            unlink('tests/streams/output.txt');
        }
        
        if(file_exists('tests/streams/error.txt'))
        {
            unlink('tests/streams/error.txt');
        }        
    }
    
    public function testStreams()
    {
        $cli = new ClearIce('tests/streams/input.txt', 'tests/streams/output.txt', 'tests/streams/error.txt');
        $this->assertEquals('Hello World', $cli->getResponse('Heck'));
        $this->assertEquals('Heck []: ', file_get_contents('tests/streams/output.txt'));
        $cli->getResponse('Flag an error', array('answers' => array('value')));
        //var_dump(file_get_contents('tests/streams/error.txt'));
    }
    
    public function testPlainEntry()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->willReturn('Hello World');
        $cli->expects($this->once())->method('output')->with('Say hello world []: ');
        $this->assertEquals('Hello World', $cli->getResponse('Say hello world'));
    }
    
    public function testDefault()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->willReturn("\n");
        $cli->expects($this->once())->method('output')->with('Some defaults [def]: ');
        $this->assertEquals('def', $cli->getResponse('Some defaults', array('default' => 'def')));
    }
    
    public function testDefaultOther()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->willReturn('other');
        $cli->expects($this->once())->method('output')->with('Some defaults [def]: ');        
        $this->assertEquals('other', $cli->getResponse('Some defaults', array('default' => 'def')));
    }
    
    public function testAnswers()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->willReturn('one');
        $cli->expects($this->once())->method('output')->with('Some answers (one/two/three) []: ');
        $this->assertEquals('one', 
            $cli->getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    )
                )
            )
        );
    }
    
    public function testAnswersDefault()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->willReturn("\n");
        $cli->expects($this->once())->method('output')->with('Some answers (one/two/three) [two]: ');
        $this->assertEquals('two', 
            $cli->getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    ),
                    'default' => 'two'
                )
            )
        );
    }
    
    public function testWrongAnswer()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output', 'error'));
        $cli->method('input')->will($this->onConsecutiveCalls("wrong", "one"));
        $cli->expects($this->at(0))->method('output')->with('Some answers (one/two/three) [two]: ');
        $cli->expects($this->once())->method('error')->with("Please provide a valid answer.\n");
        $cli->expects($this->at(3))->method('output')->with('Some answers (one/two/three) [two]: ');        
        
        $this->assertEquals('one', 
            $cli->getResponse('Some answers',
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
        $cli = $this->getMock('ClearIce', array('input', 'output', 'error'));
        $cli->method('input')->will($this->onConsecutiveCalls("", "something"));
        $cli->expects($this->at(0))->method('output')->with('Fails first []: ');
        $cli->expects($this->once())->method('error')->with("A value is required.\n");
        $cli->expects($this->at(3))->method('output')->with('Fails first []: ');          
        $this->assertEquals('something', $cli->getResponse('Fails first', array('required' => true)));        
    }
    
    public function testRequiredDefault()
    {
        $cli = $this->getMock('ClearIce', array('input', 'output'));
        $cli->method('input')->will($this->onConsecutiveCalls("", "something"));
        $cli->expects($this->at(0))->method('output')->with('Fails first [def]: ');
        $this->assertEquals('def', $cli->getResponse('Fails first', array('required' => true, 'default' => 'def')));
    }
}
