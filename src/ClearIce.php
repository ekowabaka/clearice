<?php
/**
 * A class for parsing command line arguments in PHP applications
 * 
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
 * ClearIce class.
 */
class ClearIce
{   
    /**
     *
     * @var array
     */
    private static $streams = array();
    
    private static $streamUrls = array(
        'input' => 'php://stdin',
        'output' => 'php://stdout',
        'error' => 'php://stderr'
    );
    
    private static $parser = null;
    
    /**
     * A function for getting answers to questions from users interractively.
     * @param $question The question you want to ask
     * @param $params An array of possible answers that this function should validate
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
    
    public static function setStreamUrl($type, $url)
    {
        self::$streamUrls[$type] = $url;
        unset(self::$streams[$type]);
    }
    
    /**
     * @param string $string
     */
    public static function output($string)
    {
        fputs(self::getStream('output'), $string);
    }
    
    public static function error($string)
    {
        fputs(self::getStream('error'), $string);
    }    
    
    public static function input()
    {
        return fgets(self::getStream('input'));
    }
    
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
    
    private static function getParserInstance()
    {
        if(self::$parser === null)
        {
            self::$parser = new ArgumentParser();
        }
        return self::$parser;
    }
    
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
    
    public static function reset()
    {
        self::$parser = null;
    }
}

