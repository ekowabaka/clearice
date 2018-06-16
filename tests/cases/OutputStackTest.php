<?php

use clearice\io\Io;
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
        $this->io = new Io();

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
        $this->assertEquals(Io::OUTPUT_LEVEL_1, $this->io->getOutputLevel());
        $this->io->pushOutputLevel(Io::OUTPUT_LEVEL_3);
        $this->assertEquals(Io::OUTPUT_LEVEL_3, $this->io->getOutputLevel());
        $this->io->popOutputLevel(Io::OUTPUT_LEVEL_1);
        $this->io->pushOutputLevel(Io::OUTPUT_LEVEL_3);
        $this->io->pushOutputLevel(Io::OUTPUT_LEVEL_2);
        $this->io->resetOutputLevel();
        $this->assertEquals(Io::OUTPUT_LEVEL_1, $this->io->getOutputLevel());
    }
}
