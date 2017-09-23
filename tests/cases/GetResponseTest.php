<?php

use clearice\ArgumentParser;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use clearice\ConsoleIO;

class GetResponseTest extends TestCase
{
    private $stdin;
    private $stderr;
    private $stdout;
    private $io;

    public function setup()
    {
        
        vfsStream::setup('std');
        $this->stdin = vfsStream::url('std/input');
        $this->stdout = vfsStream::url('std/output');
        $this->stderr = vfsStream::url('std/error');
        
        $this->io = new ConsoleIO();
        $this->io->setStreamUrl('input', $this->stdin);
        $this->io->setStreamUrl('output', $this->stdout);
        $this->io->setStreamUrl('error', $this->stderr);        
    }
    
    public function testStreams()
    {
        file_put_contents($this->stdin, "Hello World\nFailed\nvalue\n");        
        $this->assertEquals('Hello World', $this->io->getResponse('Heck'));
        $this->assertStringEqualsFile($this->stdout, 'Heck []: ');
        $this->io->getResponse('Flag an error', array('answers' => array('value')));
        $this->assertStringEqualsFile($this->stderr, "Please provide a valid answer.\n");
    }
    
    public function testPlainEntry()
    {
        file_put_contents($this->stdin, "Hello World");        
        $this->assertEquals('Hello World', $this->io->getResponse('Say hello world'));
    }
    
    public function testDefault()
    {
        file_put_contents($this->stdin, "\n");
        $this->assertEquals('def', $this->io->getResponse('Some defaults', array('default' => 'def')));
        $this->assertStringEqualsFile($this->stdout, 'Some defaults [def]: ');        
    }
    
    public function testDefaultOther()
    {
        file_put_contents($this->stdin, "other");
        $this->assertEquals('other', $this->io->getResponse('Some defaults', array('default' => 'def')));
        $this->assertStringEqualsFile($this->stdout, 'Some defaults [def]: ');        
    }
    
    public function testAnswers()
    {
        file_put_contents($this->stdin, "one");
        $this->assertEquals('one', 
            $this->io->getResponse('Some answers',
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
            $this->io->getResponse('Some answers',
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
            $this->io->getResponse('Some answers',
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
        $this->assertEquals('something', $this->io->getResponse('Fails first', array('required' => true)));  
        $this->assertStringEqualsFile($this->stdout, 'Fails first []: Fails first []: ');  
        $this->assertStringEqualsFile($this->stderr, "A value is required.\n");          
    }
}
