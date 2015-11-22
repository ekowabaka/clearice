<?php

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
        file_put_contents($this->stdin, "Hello World\nNot Valid");        
        $this->assertEquals('Hello World', ClearIce::getResponse('Heck'));
        $this->assertStringEqualsFile($this->stdout, 'Heck []: ');
        ClearIce::getResponse('Flag an error', array('answers' => array('value')));
        $this->assertStringEqualsFile($this->stderr, "Please provide a valid answer.\n");
    }
    
    public function testPlainEntry()
    {
        file_put_contents($this->stdin, "Hello World");        
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
    
    public function testWrongAnswer()
    {   
        file_put_contents($this->stdin, "wrong\none\n");
        
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
        $this->assertStringEqualsFile($this->stdout, 'Some answers (one/two/three) [two]: Some answers (one/two/three) [two]: ');  
        $this->assertStringEqualsFile($this->stderr, "Please provide a valid answer.\n");  
    }
    
    public function testRequired()
    {   
        file_put_contents($this->stdin, "\nsomething\n");       
        $this->assertEquals('something', ClearIce::getResponse('Fails first', array('required' => true)));  
        $this->assertStringEqualsFile($this->stdout, 'Fails first []: Fails first []: ');  
        $this->assertStringEqualsFile($this->stderr, "A value is required.\n");          
    }
    
    public function testRequiredDefault()
    {
        file_put_contents($this->stdin, "\n");       
        $this->assertEquals('def', ClearIce::getResponse('Fails first', array('required' => true, 'default' => 'def')));
        $this->assertStringEqualsFile($this->stdout, 'Fails first [def]: ');          
    }
}
