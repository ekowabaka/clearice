<?php
namespace clearice\tests\cases;

use clearice\argparser\ArgumentParser;
use clearice\argparser\CommandExistsException;
use clearice\argparser\InvalidArgumentDescriptionException;
use clearice\argparser\UnknownCommandException;
use PHPUnit\Framework\TestCase;

class ArgumentParserCommandTest extends TestCase
{
    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    public function setUp() : void
    {
        $this->argumentParser = new ArgumentParser();
        $this->argumentParser->addCommand([
            'name' => 'init',
            'help' => 'initialize a directory with the source files of the wiki'
        ]);
        $this->argumentParser->addCommand([
            'name' => 'generate',
            'help' => 'generate the data for a given directory and store it somewhere cool',
        ]);
        $this->argumentParser->addCommand([
            'name' => 'export',
            'help' => 'export wiki to a different format',
        ]);

        $this->argumentParser->addOption([
            'command' => 'init',
            'short_name' => 'd',
            'name' => 'directory',
            'type' => 'string',
            'help' => "specify the directory to be initialized"
        ]);
        $this->argumentParser->addOption([
            'command' => 'init',
            'short_name' => 't',
            'name' => 'title',
            'type' => 'string',
            'help' => "title of the new wiki to be initialized"
        ]);
        $this->argumentParser->addOption([
            'command' => 'generate',
            'short_name' => 't',
            'name' => 'target',
            'type' => 'string',
            "help" => "specify where the generated wiki should be"
        ]);
        $this->argumentParser->addOption([
            'command' => 'export',
            'short_name' => 'f',
            'name' => 'format',
            "help" => "specify the format of the exported wiki"
        ]);
        $this->argumentParser->addOption([
            'short_name' => 'v',
            'name' => 'verbose',
            "help" => "display more information"
        ]);
    }

    public function testCommandInvocation()
    {
        $this->assertEquals(['__command' => 'init', '__executed' => 'app'], $this->argumentParser->parse(["app", "init"]));
        $this->assertEquals(
            ['__command' => 'init', 'directory' => '/some/path', '__executed' => 'app'],
            $this->argumentParser->parse(["app", "init", "--directory", "/some/path"])
        );
    }

    public function testCommandlessFlagInvocation()
    {
        $this->assertEquals(
            ['__command' => 'generate', 'verbose' => true, '__executed' => 'app'], 
            $this->argumentParser->parse(['app', 'generate', '--verbose'])
        );
    }

    public function testUnknownCommandException()
    {
        $this->expectException(UnknownCommandException::class);
        $this->argumentParser->addOption(['command' => 'download', 'name' => 'url']);
    }

    public function testInvalidArgumentDescriptionException()
    {
        $this->expectException(InvalidArgumentDescriptionException::class);
        $this->argumentParser->addCommand(['help' => 'I forgot the name or mis-spelled it']);
    }

    public function testCommandExistsException()
    {
        $this->expectException(CommandExistsException::class);
        $this->argumentParser->addCommand(['name' => 'init']);
    }
}
