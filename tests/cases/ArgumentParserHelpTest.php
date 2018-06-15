<?php

namespace ntentan\tests\cases;

use clearice\argparser\ArgumentParser;
use clearice\argparser\HelpMessageGenerator;
use PHPUnit\Framework\TestCase;

class ArgumentParserHelpTest extends TestCase
{
    private $argumentParser;

    public function setUp()
    {
        $helpGeneratorMock = $this->createMock(HelpMessageGenerator::class);
        $helpGeneratorMock->expects($this->once())->method('generate');
        $this->argumentParser = new ArgumentParser($helpGeneratorMock);
    }

    public function testAutoHelpCall()
    {
        $this->argumentParser->addOption([
            'short_name' => 'i',
            'name' => 'input',
            'type' => 'string',
            'help' => "specifies where the input files for the wiki are found."
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'o',
            'name' => 'output',
            'type' => 'string',
            "help" => "specifies where the wiki should be written to"
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'v',
            'name' => 'verbose',
            "help" => "displays detailed information about everything that happens"
        ]);

        $this->argumentParser->enableHelp('test', 'This is a test application', 'Report bugs to');
        $this->assertEquals(['help' => true], $this->argumentParser->parse(['app', '--help']));
    }
}