<?php
namespace clearice\tests\cases;

use clearice\argparser\ArgumentParser;
use clearice\argparser\HelpMessageGenerator;
use clearice\argparser\InvalidArgumentDescriptionException;
use clearice\argparser\InvalidValueException;
use clearice\argparser\OptionExistsException;
use PHPUnit\Framework\TestCase;

class ArgumentParserTest extends TestCase
{
    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    public function setUp() : void
    {
        $this->argumentParser = new ArgumentParser();
    }

    public function testParse()
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

        $this->assertEquals(['input' => 'good', '__executed' => 'app'], $this->argumentParser->parse(["app", "--input", "good"]));
        $this->assertEquals(['verbose' => true, '__executed' => 'app'], $this->argumentParser->parse(["app", "--verbose"]));
        $this->assertEquals(['output' => 'good', '__executed' => 'app'], $this->argumentParser->parse(["app", "--output=good"]));

        $this->assertEquals(['input' => 'good', '__executed' => 'app'], $this->argumentParser->parse(["app", "-i", "good"]));
        $this->assertEquals(['verbose' => true, '__executed' => 'app'], $this->argumentParser->parse(["app", "-v"]));
        $this->assertEquals(['output' => 'good', '__executed' => 'app'], $this->argumentParser->parse(["app", "-ogood"]));

        $this->assertEquals(['__args' => ['other', 'arguments'], '__executed' => 'app'], $this->argumentParser->parse(["app", "other", "arguments"]));
    }

    /**
     * @expectedException \clearice\argparser\OptionExistsException
     * @expectedExceptionMessage An argument option with short_name  i already exists.
     */
    public function testShortOptionExistsException()
    {
        $this->expectException(OptionExistsException::class);
        $this->expectExceptionMessage("An argument option with short_name  i already exists.");
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'input']);
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'index']);
    }

    public function testLongOptionExistsException()
    {
        $this->expectException(OptionExistsException::class);
        $this->expectExceptionMessage("An argument option with name  input already exists.");
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'input']);
        $this->argumentParser->addOption(['name' => 'input']);
    }

    public function testNoNameException()
    {
        $this->expectException(InvalidArgumentDescriptionException::class);
        $this->argumentParser->addOption(['help' => 'Does not have a valid name']);
    }

    public function testInvalidValueException()
    {
        $this->expectException(InvalidValueException::class);
        $this->argumentParser->addOption(['name' => 'input', 'type' => 'string']);
        $this->argumentParser->parse(["add", "--input"]);
    }

    public function testInvalidValueException2()
    {
        $this->expectException(InvalidValueException::class);
        $this->argumentParser->addOption(['name' => 'input', 'short_name' => 'i', 'type' => 'string']);
        $this->argumentParser->parse(["add", "-i"]);
    }

    public function testValidation()
    {
        $this->argumentParser->addOption([
            'short_name' => 'i',
            'name' => 'input',
            'type' => 'string',
            'repeats' => true,
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

        $this->assertEquals(["input" => ["/path/1", "/path/2"], '__executed' => 'app'], $this->argumentParser->parse(["app", "--input", "/path/1", "--input", "/path/2"]));
    }

    public function testDefaults()
    {
        $this->argumentParser->addOption([
            'short_name' => 'i',
            'name' => 'input',
            'type' => 'string',
            'default' => 'in',
            'help' => "specifies where the input files for the wiki are found."
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'o',
            'name' => 'output',
            'type' => 'string',
            'default' => 'out',
            "help" => "specifies where the wiki should be written to"
        ]);

        $this->argumentParser->addOption([
            'short_name' => 'v',
            'name' => 'verbose',
            "help" => "displays detailed information about everything that happens"
        ]);

        $this->assertEquals(["input" => "in", "output" => "out", '__executed' => 'app'], $this->argumentParser->parse(["app"]));
        $this->assertEquals(["input" => "in2", "output" => "out", '__executed' => 'app'], $this->argumentParser->parse(["app", "-iin2"]));
    }

    public function testGetHelp()
    {
        $this->argumentParser->addCommand(
            [
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

        $desciption = "Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.";
        $footer = "Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld";
        $this->argumentParser->enableHelp($desciption, $footer, "app");

        $this->assertEquals(file_get_contents('tests/data/help-with-commands.txt'), $this->argumentParser->getHelpMessage());
    }

    public function testAutoHelpCall()
    {
        $helpGeneratorMock = $this->createMock(HelpMessageGenerator::class);
        $helpGeneratorMock->expects($this->once())->method('generate');
        //$programControlMock = $this->createMock(ProgramControl::class);
        //$programControlMock->expects($this->once())->method('quit');

        $this->argumentParser = new ArgumentParser($helpGeneratorMock);
        $this->argumentParser->setExitCallback(function($code) {
            $this->assertEquals(0, $code);
        });

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
        $this->assertEquals(null, $this->argumentParser->parse(['app', '--help']));
    }
}
