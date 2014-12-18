<?php

use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

class OutputLevelsTest extends PHPUnit_Framework_TestCase
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
    
    public function testSetGetLevels()
    {
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_0);
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_0, ClearIce::getOutputLevel());
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_1);
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_1, ClearIce::getOutputLevel());
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_2);
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_2, ClearIce::getOutputLevel());
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_3);
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_3, ClearIce::getOutputLevel());
    }
    
    public function testLevelThreshold()
    {
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_0);
        ClearIce::output("Hello");
        $this->assertFileNotExists($this->stdout);
        ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_1);
        ClearIce::output("Hello\n");
        $this->assertStringEqualsFile($this->stdout, "Hello\n");
    }
}
