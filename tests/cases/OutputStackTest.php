<?php

use clearice\ClearIce;
use org\bovigo\vfs\vfsStream;

class OutputStackTest extends PHPUnit_Framework_TestCase
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

    public function testPushPopLevel()
    {
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_1, ClearIce::getOutputLevel());
        ClearIce::pushOutputLevel(ClearIce::OUTPUT_LEVEL_3);
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_3, ClearIce::getOutputLevel());
        ClearIce::popOutputLevel(ClearIce::OUTPUT_LEVEL_1);
        ClearIce::pushOutputLevel(ClearIce::OUTPUT_LEVEL_3);
        ClearIce::pushOutputLevel(ClearIce::OUTPUT_LEVEL_2);
        ClearIce::resetOutputLevel();
        $this->assertEquals(ClearIce::OUTPUT_LEVEL_1, ClearIce::getOutputLevel());
    }
}
