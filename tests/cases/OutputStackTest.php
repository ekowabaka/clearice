<?php

use clearice\ConsoleIO;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class OutputStackTest extends TestCase
{
    private $stdin;
    private $stderr;
    private $stdout;
    private $io;

    public function setup()
    {
        $this->io = new ConsoleIO();
        
        vfsStream::setup('std');
        $this->stdin = vfsStream::url('std/input');
        $this->stdout = vfsStream::url('std/output');
        $this->stderr = vfsStream::url('std/error');
        
        $this->io->setStreamUrl('input', $this->stdin);
        $this->io->setStreamUrl('output', $this->stdout);
        $this->io->setStreamUrl('error', $this->stderr);        
    }

    public function testPushPopLevel()
    {
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_1, $this->io->getOutputLevel());
        $this->io->pushOutputLevel(ConsoleIO::OUTPUT_LEVEL_3);
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_3, $this->io->getOutputLevel());
        $this->io->popOutputLevel(ConsoleIO::OUTPUT_LEVEL_1);
        $this->io->pushOutputLevel(ConsoleIO::OUTPUT_LEVEL_3);
        $this->io->pushOutputLevel(ConsoleIO::OUTPUT_LEVEL_2);
        $this->io->resetOutputLevel();
        $this->assertEquals(ConsoleIO::OUTPUT_LEVEL_1, $this->io->getOutputLevel());
    }
}
