<?php
require_once "ClearIce.php";

error_reporting(E_ALL ^ E_NOTICE);

class GetResponseTest extends PHPUnit_Framework_TestCase
{
    public function testPlainEntry()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn('Hello World');
        ob_start();
        $this->assertEquals('Hello World', $cli->getResponse('Say hello world'));
        $output = ob_get_clean();
        $this->assertEquals('Say hello world []: ', $output);
    }
    
    public function testDefault()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn("\n");
        ob_start();
        $this->assertEquals('def', $cli->getResponse('Some defaults', array('default' => 'def')));
        $output = ob_get_clean();
        $this->assertEquals('Some defaults [def]: ', $output);
    }
    
    public function testDefaultOther()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn('other');
        ob_start();
        $this->assertEquals('other', $cli->getResponse('Some defaults', array('default' => 'def')));
        $output = ob_get_clean();
        $this->assertEquals('Some defaults [def]: ', $output);
    }
    
    public function testAnswers()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn('one');
        ob_start();
        $this->assertEquals('one', 
            $cli->getResponse('Some answers',
                array(
                    'answers' => array(
                        'one', 'two', 'three'
                    )
                )
            )
        );
        $output = ob_get_clean();
        $this->assertEquals('Some answers (one/two/three) []: ', $output);
    }
    
    public function testAnswersDefault()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn("\n");
        ob_start();
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
        $output = ob_get_clean();
        $this->assertEquals('Some answers (one/two/three) [two]: ', $output);        
    }
    
    public function testWrongAnswer()
    {
        $cli = $this->getMock('ClearIce', array('input'));
        $cli->method('input')->willReturn("wrong");
        ob_start();
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
        $output = ob_get_clean();
        $this->assertEquals('Some answers (one/two/three) [two]: ', $output);        
    }    
}
