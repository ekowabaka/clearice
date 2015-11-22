<?php
/*
 * ClearIce CLI Argument Parser
 * Copyright (c) 2012-2015 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2012-2014 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace clearice;

/**
 * The ClearIce class forms the static entry for the entire library. 
 * All operations of the library are done through this class. Being static, 
 * the class contains sigleton objects with which it performs all its operations.
 */
class ClearIce
{
    /**
     * Least output level.
     * At this level clearice is expected to be mute. Nothing would be outputted
     * to any of the streams.
     * @var int
     */
    const OUTPUT_LEVEL_0 = 0;
    
    /**
     * Output level 1
     * @var int
     */
    const OUTPUT_LEVEL_1 = 1;
    
    /**
     * Output level 2
     * @var int
     */
    const OUTPUT_LEVEL_2 = 2;
    
    /**
     * Output level 3.
     * At this level clearice is expected not to filter any output. Everything
     * that is sent to ClearIce would be outputted to the streams.
     * @var int
     */
    const OUTPUT_LEVEL_3 = 3;
    
    /**
     * The default output level of the ClearIce library.
     * @var int
     */
    private static $defaultOutputLevel = self::OUTPUT_LEVEL_1;
    
    /**
     * An array to hold the output level stack.
     * @var array
     */
    private static $outputLevelStack = array();
    
    /**
     * An array of the three streams used primarily for I/O. These are the
     * standard output stream, the standard input stream and the error stream.
     * Being an associative array, this property presents the three streams
     * through its output, input and error keys.
     * 
     * @var array
     */
    private static $streams = array();
    
    /**
     * The URLs of the various streams used for I/O. This variable stores these
     * URLs under the input, output and error streams respectively. 
     * 
     * @see ClearIce::$streams
     * @var array
     */
    private static $streamUrls = array(
        'input' => 'php://stdin',
        'output' => 'php://stdout',
        'error' => 'php://stderr'
    );
    
    /**
     * An instance of the ArgumentParser class which is maintained as a singleton
     * for the purposes of parsing command line arguments.
     * 
     * @var \clearice\ArgumentParser
     */
    private static $parser = null;
    
    /**
     * A function for getting answers to questions from users interractively.
     * This function takes the question and an optional array of parameters. 
     * The question is a regular string and the array provides extra information
     * about the question being asked.
     * 
     * The array takes the following parameters
     * 
     * **answers**  
     * An array of posible answers to the question. Once this array is available
     * the user would be expected to provide an answer which is specifically in
     * the list. Any other answer would be rejected. The library would print
     * out all the possible answers so the user is aware of which answers
     * are valid.
     * 
     * **default**  
     * A default answer which should be used in case the user does not supply an
     * answer. The library would make the user aware of this default by placing
     * it in square brackets after the question.
     * 
     * **required**  
     * If this flag is set, the user would be required to provide an answer. A
     * blank answer would be rejected.
     * 
     * @param string $question The question you want to ask
     * @param array  $params   An array of options that this function takes.
     * @return string The response provided by the user to the prompt.
     */
    public static function getResponse($question, $params = array())
    {
        self::cleanResponseParams($params);
        $prompt = $question;
        if(count($params['answers']) > 0) {
            $prompt .= " (" . implode("/", $params['answers']) . ")";
        }

        self::output($prompt . " [{$params['default']}]: ");
        $response = str_replace(array("\n", "\r"),array("",""), self::input());

        if($response == "" && $params['required'] === true && $params['default'] == '')
        {
            self::error("A value is required.\n");
            return self::getResponse($question, $params);
        }
        else if($response == "" && $params['required'] === true && $params['default'] != '')
        {
            return $params['default'];
        }
        else if($response == "")
        {
            return $params['default'];
        }
        else
        {
            if(count($params['answers']) == 0)
            {
                return $response;
            }
            foreach($params['answers'] as $answer)
            {
                if(strtolower($answer) == strtolower($response))
                {
                    return strtolower($answer);
                }
            }
            self::error("Please provide a valid answer.\n");
            return self::getResponse($question, $params);
        }
    } 
    
    /**
     * Set the URL of any of the streams used by ClearIce.
     * ClearIce maintains three different streams for its I/O operations. The
     * `output` stream is used for output, the `error` stream is used for errors 
     * and the `input` stream is used for input. The `output` and `error` streams
     * are represented by the standard output and standard error streams 
     * respectively. The `input` stream on the other hand is represented by the 
     * standard input stream by default. 
     * 
     * Streams could be any valid PHP stream URL.
     * Example to write all output to a file you could set.
     * 
     * ````php
     * <?php
     * ClearIce::setStreamUrl('output', '/path/to/file');
     * ClearIce::setStreamUrl('error', '/path/to/file');
     * ````
     * 
     * Once a new URL is set, any old streams are closed and the new one is 
     * opened in its place immediately the stream is accessed.
     * 
     * @param string $type The type of stream to set a URL for. The value of this 
     *                     could either be 'error', 'input' or 'output'.
     * 
     * @param string $url  The URL of the stream. Based on the type of stream
     *                     being requested, the right kind of permissions must
     *                     be set. For instance 
     */
    public static function setStreamUrl($type, $url)
    {
        self::$streamUrls[$type] = $url;
        unset(self::$streams[$type]);
    }
    
    /**
     * Safely EXIT the app. 
     * Usefull if testing so that the termination doesn't kill the test 
     * environment. 
     */
    public static function terminate()
    {
        if(!defined('TESTING')) die();
    }
    
    /**
     * Write a string to the output stream. 
     * If an output stream is not defined this method writes to the standard 
     * output (the console) by default.
     * 
     * @param string $string
     */
    public static function output($string, $outputLevel = self::OUTPUT_LEVEL_1, $stream = 'output')
    {
        if($outputLevel <= self::$defaultOutputLevel)
        {
            fputs(self::getStream($stream), $string);
        }
    }
    
    /**
     * Write a string to the error stream. 
     * If an error stream is not defined this method writes to the standard 
     * error (the console) by default.
     * 
     * @param string $string
     */    
    public static function error($string, $outputLevel = self::OUTPUT_LEVEL_1)
    {
        self::output($string, $outputLevel, 'error');
    }    
    
    /**
     * Set the output level of the ClearIce output streams (including the error)
     * stream. 
     * 
     * @param int $outputLevel
     */
    public static function setOutputLevel($outputLevel)
    {
        self::$defaultOutputLevel = $outputLevel;
    }
    
    /**
     * Returns the current output level of the CliearIce library.
     * @return int
     */
    public static function getOutputLevel()
    {
        return self::$defaultOutputLevel;
    }
    
    /**
     * Push an output level unto the output level stack.
     * The output level pushed becomes the new output level with which ClearIce
     * would work. The previous level pushed would be automatically restored
     * when the ClearIce::popOutputLevel() method is called. Using the output
     * level stack gives you a convenient way to change the output level 
     * temporarily without having to keep a record of the previous output level.
     * 
     * @param int $outputLevel
     */
    public static function pushOutputLevel($outputLevel)
    {
        self::$outputLevelStack[] = self::getOutputLevel();
        self::setOutputLevel($outputLevel);
    }
    
    /**
     * Pop the last output level which was pushed unto the output level stack.
     * This restores the previous output level which was active before the last
     * call to the ClearIce::pushOutputLevel() method. Using the output
     * level stack gives you a convenient way to change the output level 
     * temporarily without having to keep a record of the previous output level.
     * 
     */
    public static function popOutputLevel()
    {
        self::setOutputLevel(array_pop(self::$outputLevelStack));
    }
    
    /**
     * Resets the output level stack.
     * This method clears all items off the output level stack leaving only the
     * current output level.
     */
    public static function resetOutputLevel()
    {
        if(count(self::$outputLevelStack) > 0)
        {
            self::setOutputLevel(reset(self::$outputLevelStack));
            self::$outputLevelStack = array();
        }
    }

    /**
     * Reads a line of string from the input stream. 
     * If an input stream is not defined this method reads an input from the 
     * standard input (usually a keyboard) by default.
     * 
     * @todo look into using readline for this in cases where it's available
     * @return string
     */
    public static function input()
    {
        return fgets(self::getStream('input'));
    }
    
    /**
     * Returns a stream resource for a given stream type. 
     * If the stream has not been opened this method opens the stream before 
     * returning the asociated resource. This ensures that there is only one 
     * resource handle to any stream at any given time.
     * 
     * @param string $type
     * @return resource
     */
    private static function getStream($type)
    {
        if(!isset(self::$streams[$type]))
        {
            self::$streams[$type] = fopen(self::$streamUrls[$type], $type == 'input' ? 'r' : 'w');
        }
        return self::$streams[$type];
    }
    
    /**
     * Adds commands which are to be recognized during argument parsing.
     * Commands to be added could be passed as strings or structured arrays. 
     * This method takes as many arguments as possible. 
     * 
     * For example you could add commands with ...
     * 
     * ````php
     * <?php
     * ClearIce::addCommands('start', 'stop', 'restart');
     * ````
     * 
     * ... or more expressively ...
     * 
     * ````php
     * <?php
     * ClearIce::addCommands(
     *     array(
     *         'command' => 'start',
     *         'help' => 'start a new instance of the server'
     *     ),
     *     array(
     *         'command' => 'stop',
     *         'help' => 'stop the current instance of the server'
     *     ),
     *     array(
     *         'command' => 'restart',
     *         'help' => 'restart the current instance of the server'
     *     )
     * );
     * ````
     * 
     * A string argument would be taken as the text for the command. An 
     * array argument could have a combination of `command`, `help`, `class` and 
     * `usage` keys to help provide a more detailed command description. 
     * The `command` key stores the command text, the `help` key
     * stores the help message (which would be displayed in cases where
     * ClearIce's automatic help feature is used), the `usage` key specifies a 
     * usage syntax (which would also be displayed when the automatic help feature
     * is used) and the `class` key is the name of a class which implements the 
     * `Command` interface. The class specified in the `class` key would 
     * automatically be inistantiated when ClearIce is parsing the command line 
     * arguments. The options parsed on the command line would be passed on to 
     * the new object created.
     * 
     * @param string|array $command The command to be added for parsing.
     */
    public static function addCommands($command)
    {
        self::callParserMethod('addCommands', func_get_args());
    }
    
    /**
     * Add an option to be recognized by the ClearIce parser.
     * Options added could be passed as strings or structured arrays. This method
     * takes as many arguments as needed. Options can also be tied to 
     * specific commands so they remain valid only when those commands
     * are specified.
     * 
     * Options can be added with
     * 
     * ````php
     * <?php
     * ClearIce::addOptions('input', 'output', 'format')
     * ````
     * 
     * ... or more expressively ...
     * 
     * ````php
     * <?php
     * ClearIce::addOptions(
     *     array(
     *         'short' => 'i',
     *         'long' => 'input',
     *         'has_value' => true,
     *         'help' => "specifies where the input files for the wiki are found.",
     *         'command' => 'generate'
     *     ),
     *     array(
     *         'short' => 'o',
     *         'long' => 'output',
     *         'has_value' => true,
     *         "help" => "specifies where the wiki should be written to",
     *         'command' => 'generate'
     *     )
     * );
     * ````
     * 
     * A string argument would be taken as the long version of the option. An
     * array argument must either have a `short` key, a `long` key or both. The
     * `long` key represents the long version of the option and the `short` key 
     * would hold a single character which represents a short form of the long
     * option. In addition to the `long` and `short` keys, you can also pass
     * a combination of any of the `has_value`, `help`, `command` or `value`
     * keys.
     * 
     * The `has_value` key tells the argument parser that the option takes
     * a value. The `help` key is a short help message which (whoudl be displayed
     * when the automatic help feature is used). The `command` key specifies the 
     * name of a command to which the option should be associated. The `value`
     * key is a short description used when generating help messages to give
     * the user an idea of the kind of value the option takes.
     * 
     */
    public static function addOptions()
    {
        self::callParserMethod('addOptions', func_get_args());
    }
    
    /**
     * Parse the command line arguments passed to the app.
     * This method parses the command line argumens and returns array which 
     * represents the options that were detected. The parse method also
     * instantiates and executes classes for commands which have specified
     * a `Command` class.
     * 
     * @return array A structured array which contains options as keys and the
     *       values assigned to the options as array values. Options which do not 
     *       accept value would have true assigned.
     */
    public static function parse()
    {
        return self::getParserInstance()->parse();
    }
    
    /**
     * Set a usage hint for your application.
     * The usage hint specified would be displayed when the automatic help 
     * feature of the library is used. Either a single line string or an array
     * of strings could be passed to this method. Usage strings passed as arrays
     * would be properly formatted into a multi-line usage hint format.
     * 
     * @param string|array $usage A short usage hint for the application.
     */
    public static function setUsage($usage)
    {
        self::getParserInstance()->setUsage($usage);
    }
    
    /**
     * Set a description for your application.
     * The description specified would be displayed when the automatic help
     * feature of the library is used. This description text is described before
     * the help message and as such can include anything from the name of the
     * application, copyright information to ACII art graphics.
    * 
     * @param string $description Text for the description message.
     */
    public static function setDescription($description)
    {
        self::getParserInstance()->setDescription($description);
    }
    
    /**
     * Set a foonote for the help message of your application.
     * The footnote specified would be displayed at the bottom of the automatic
     * help message generated. This text could contain information about where
     * to find more help, how to report bugs or you could also put some awesome
     * ASCII art here.
     * 
     * @param string $footnote Text for the footnote message
     */
    public static function setFootnote($footnote)
    {
        self::getParserInstance()->setFootnote($footnote);
    }
    
    /**
     * Add the automatic help options.
     * This would add the `-h` and `--help` options to your application. When
     * these options are passed, the library would automatically display a help
     * message to the user. The help options are also added to commands so
     * users can get help which are specific to commands.
     */
    public static function addHelp()
    {
        self::getParserInstance()->addHelp();
    }
    
    /**
     * Generate and return a help message.
     * This method generates and returns a help message based on the various
     * options and commands passed to the library. This method is used internally
     * to generate and display the automatic help message. Note however that this
     * message would still be active even when the automatic help message has
     * not been activated.
     * 
     * @param string $command Generate the help message for the command specified 
     *     or null to generate help for the application.
     * @return string
     */
    public static function getHelpMessage($command = '')
    {
        return self::getParserInstance()->getHelpMessage($command);
    }
    
    /**
     * Sets the parser into strict mode.
     * A strict parser would terminate the application if it doesn't understand 
     * any options. A not-strict parser would just return the unknown options it 
     * encountered and expect the application to deal with it appropriately. 
     * When a parser is in strict mode, ClearIce prints a descriptive help 
     * message which advises the user about the unknown options. In cases where 
     * the help feature has been enabled, ClearIce would go ahead to advice the 
     * user to request for help.
     * 
     * @param bool $strict
     */
    public static function setStrict($strict)
    {
        self::getParserInstance()->setStrict($strict);
    }
    
    /**
     * Returns a singleton instance of the argument parser.
     * 
     * @return \clearice\ArgumentParser
     */
    private static function getParserInstance()
    {
        if(self::$parser === null)
        {
            self::$parser = new ArgumentParser();
        }
        return self::$parser;
    }
    
    /**
     * @param string $name The name of the method called
     * @param array $arguments An array of arguments passed to the method
     * @return mixed
     * @throws \Exception
     */
    private static function callParserMethod($name, $arguments)
    {
        $parser = self::getParserInstance();
        $method = new \ReflectionMethod($parser, $name);
        return $method->invokeArgs($parser, $arguments);            
    }
    
    /**
     * Reset the library. Deletes all singletons and provides you with a fresh
     * class ... sort of! This method is primarily used during testing to 
     * refresh the class in between tests.
     */
    public static function reset()
    {
        self::$parser = null;
    }
    
    private static function cleanResponseParams(&$params)
    {
        $params['answers'] = isset($params['answers']) ? $params['answers'] : [];
        $params['default'] = isset($params['default']) ? $params['default'] : '';
        $params['required'] = isset($params['required']) ? $params['required'] : false;
    }
    
    public static function addGroups()
    {
        self::callParserMethod('addGroups', func_get_args());
    }
}

