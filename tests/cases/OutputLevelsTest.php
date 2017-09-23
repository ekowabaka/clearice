<?php

use clearice\ConsoleIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class OutputLevelsTest extends TestCase
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
        
        $this->io = new \clearice\ConsoleIO();
        $this->io->setStreamUrl('input', $this->stdin);
        $this->io->setStreamUrl('output', $this->stdout);
        $this->io->setStreamUrl('error', $this->stderr);        
    }
    
    public function testSetGetLevels()
    {
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_0);
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_0, $this->io->getOutputLevel());
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_1);
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_1, $this->io->getOutputLevel());
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_2);
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_2, $this->io->getOutputLevel());
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_3);
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_3, $this->io->getOutputLevel());
    }
    
    public function testLevelThreshold()
    {
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_0);
        $this->io->output("Hello");
        $this->assertFileNotExists($this->stdout);
        $this->io->setOutputLevel(ConsoleIO::OUTPUT_LEVEL_1);
        $this->io->output("Hello\n");
        $this->assertStringEqualsFile($this->stdout, "Hello\n");
    }
}
