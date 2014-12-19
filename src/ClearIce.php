<?php
/**
 * ClearIce CLI Argument Parser
 * Copyright (c) 2012-2014 James Ekow Abaka Ainooson
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
 * the class maintains singleton objects with which it performs all its 
 * operations.
 * 
 * @method array parse() Parse the command line arguments. 
 *          This method parses the command line arguments and return a structured array which
 *          represents the options which were interpreted by ClearIce. The array
 *          returned has the following structure.
 */
class ClearIce
{   
    const OUTPUT_LEVEL_0 = 0;
    
    const OUTPUT_LEVEL_1 = 1;
    
    const OUTPUT_LEVEL_2 = 2;
    
    const OUTPUT_LEVEL_3 = 3;
    
    private static $defaultOutputLevel = self::OUTPUT_LEVEL_1;
    
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
     * @param $question The question you want to ask
     * @param $params An array of options that this function takes.
     */
    public static function getResponse($question, $params = array())
    {
        $prompt = $question;
        if(is_array($params['answers']))
        {
            if(count($params['answers']) > 0) {
                $prompt .= " (" . implode("/", $params['answers']) . ")";
            }
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
     * Set the URL of a particular stream. Once a new URL is set, any old streams
     * are closed and the new one is opened in its place.
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
     * Safely EXIT the app. Usefull if testing so that the termination doesn't 
     * kill the test environment. 
     */
    public static function terminate()
    {
        if(!defined('TESTING')) die();
    }
    
    /**
     * Write a string to the output stream. If an output stream is not defined
     * this method writes to the standard output (the console) by default.
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
     * Write a string to the error stream. If an error stream is not defined
     * this method writes to the standard error (the console) by default.
     * 
     * @param string $string
     */    
    public static function error($string, $outputLevel = self::OUTPUT_LEVEL_1)
    {
        self::output($string, $outputLevel, 'error');
    }    
    
    public static function setOutputLevel($outputLevel)
    {
        self::$defaultOutputLevel = $outputLevel;
    }
    
    public static function getOutputLevel()
    {
        return self::$defaultOutputLevel;
    }

    /**
     * Reads a line from the input stream. If an input stream is not defined
     * this method reads an input from the standard input (usually a keyboard)
     * by default.
     * 
     * @todo look into using readline for this in cases where it's available
     * @return string
     */
    public static function input()
    {
        return fgets(self::getStream('input'));
    }
    
    /**
     * Returns a stream resource for a given stream type. If the stream has not
     * been opened this method opens the stream before returning the asociated
     * resource. This ensures that there is only one resource handle to any 
     * stream at any given time.
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
    
    private static $parserMethods = array(
        'addCommands',
        'addOptions',
        'setUsage',
        'parse',
        'setDescription',
        'setFootnote',
        'addHelp',
        'getHelpMessage',
        'setStrict'
    );
    
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
    public static function __callStatic($name, $arguments)
    {
        if(array_search($name, self::$parserMethods) === false)
        {
            throw new \Exception("Unknown method $name");
        }
        else
        {
            $parser = self::getParserInstance();
            $method = new \ReflectionMethod($parser, $name);
            return $method->invokeArgs($parser, $arguments);            
        }
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
}

