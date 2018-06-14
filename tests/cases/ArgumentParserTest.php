<?php
/**
 * Created by PhpStorm.
 * User: ekow
 * Date: 6/12/18
 * Time: 5:36 AM
 */

namespace cases;


use clearice\argparser\ArgumentParser;
use clearice\argparser\HelpMessageGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

class ArgumentParserTest extends TestCase
{
    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    public function setup()
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

        $this->assertEquals(['input' => 'good'], $this->argumentParser->parse(["app", "--input", "good"]));
        $this->assertEquals(['verbose' => true], $this->argumentParser->parse(["app", "--verbose"]));
        $this->assertEquals(['output' => 'good'], $this->argumentParser->parse(["app", "--output=good"]));

        $this->assertEquals(['input' => 'good'], $this->argumentParser->parse(["app", "-i", "good"]));
        $this->assertEquals(['verbose' => true], $this->argumentParser->parse(["app", "-v"]));
        $this->assertEquals(['output' => 'good'], $this->argumentParser->parse(["app", "-ogood"]));

        $this->assertEquals(['__args' => ['other', 'arguments']], $this->argumentParser->parse(["app", "other", "arguments"]));
    }

    /**
     * @expectedException \clearice\argparser\OptionExistsException
     * @expectedExceptionMessage An argument option with short_name  i already exists.
     */
    public function testShortOptionExistsException()
    {
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'input']);
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'index']);
    }

    /**
     * @expectedException \clearice\argparser\OptionExistsException
     * @expectedExceptionMessage An argument option with name  input already exists.
     */
    public function testLongOptionExistsException()
    {
        $this->argumentParser->addOption(['short_name' => 'i', 'name' => 'input']);
        $this->argumentParser->addOption(['name' => 'input']);
    }

    /**
     * @expectedException \clearice\argparser\InvalidArgumentDescriptionException
     */
    public function testNoNameException()
    {
        $this->argumentParser->addOption(['help' => 'Does not have a valid name']);
    }

    /**
     * @expectedException \clearice\argparser\InvalidValueException
     */
    public function testInvalidValueException()
    {
        $this->argumentParser->addOption(['name' => 'input', 'type' => 'string']);
        $this->argumentParser->parse(["add", "--input"]);
    }

    /**
     * @expectedException \clearice\argparser\InvalidValueException
     */
    public function testInvalidValueException2()
    {
        $this->argumentParser->addOption(['name' => 'input', 'short_name' => 'i', 'type' => 'string']);
        $this->argumentParser->parse(["add", "-i"]);
    }
}
