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
 * The ClearIce class forms the entry for the entire library. 
 * All operations of the library are done through this class. Being static, 
 * the class contains sigleton objects with which it performs all its operations.
 */
class ConsoleIO
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
    private $defaultOutputLevel = self::OUTPUT_LEVEL_1;

    /**
     * An array to hold the output level stack.
     * @var array
     */
    private $outputLevelStack = array();

    /**
     * An array of the three streams used primarily for I/O. These are the
     * standard output stream, the standard input stream and the error stream.
     * Being an associative array, this property presents the three streams
     * through its output, input and error keys.
     * 
     * @var array
     */
    private $streams = array();

    /**
     * The URLs of the various streams used for I/O. This variable stores these
     * URLs under the input, output and error streams respectively. 
     * 
     * @see ClearIce::$streams
     * @var array
     */
    private $streamUrls = array(
        'input' => 'php://stdin',
        'output' => 'php://stdout',
        'error' => 'php://stderr'
    );

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
    public function getResponse($question, $params = array())
    {
        $this->cleanResponseParams($params);
        $prompt = $question;
        if (count($params['answers']) > 0) {
            $prompt .= " (" . implode("/", $params['answers']) . ")";
        }

        $this->output($prompt . " [{$params['default']}]: ");
        $response = str_replace(array("\n", "\r"), array("", ""), $this->input());

        if ($response == "" && $params['required'] === true && $params['default'] == '') {
            $this->error("A value is required.\n");
            return $this->getResponse($question, $params);
        } else if ($response == "" && $params['required'] === true && $params['default'] != '') {
            return $params['default'];
        } else if ($response == "") {
            return $params['default'];
        } else {
            if (count($params['answers']) == 0) {
                return $response;
            }
            foreach ($params['answers'] as $answer) {
                if (strtolower($answer) == strtolower($response)) {
                    return strtolower($answer);
                }
            }
            $this->error("Please provide a valid answer.\n");
            return $this->getResponse($question, $params);
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
    public function setStreamUrl($type, $url)
    {
        $this->streamUrls[$type] = $url;
        unset($this->streams[$type]);
    }

    /**
     * Write a string to the output stream. 
     * If an output stream is not defined this method writes to the standard 
     * output (the console) by default.
     * 
     * @param string $string
     */
    public function output($string, $outputLevel = self::OUTPUT_LEVEL_1, $stream = 'output')
    {
        if ($outputLevel <= $this->defaultOutputLevel) {
            fputs($this->getStream($stream), $string);
        }
    }

    /**
     * Write a string to the error stream. 
     * If an error stream is not defined this method writes to the standard 
     * error (the console) by default.
     * 
     * @param string $string
     */
    public function error($string, $outputLevel = self::OUTPUT_LEVEL_1)
    {
        $this->output($string, $outputLevel, 'error');
    }

    /**
     * Set the output level of the ClearIce output streams (including the error)
     * stream. 
     * 
     * @param int $outputLevel
     */
    public function setOutputLevel($outputLevel)
    {
        $this->defaultOutputLevel = $outputLevel;
    }

    /**
     * Returns the current output level of the CliearIce library.
     * @return int
     */
    public function getOutputLevel()
    {
        return $this->defaultOutputLevel;
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
    public function pushOutputLevel($outputLevel)
    {
        $this->outputLevelStack[] = $this->getOutputLevel();
        $this->setOutputLevel($outputLevel);
    }

    /**
     * Pop the last output level which was pushed unto the output level stack.
     * This restores the previous output level which was active before the last
     * call to the ClearIce::pushOutputLevel() method. Using the output
     * level stack gives you a convenient way to change the output level 
     * temporarily without having to keep a record of the previous output level.
     * 
     */
    public function popOutputLevel()
    {
        $this->setOutputLevel(array_pop($this->outputLevelStack));
    }

    /**
     * Resets the output level stack.
     * This method clears all items off the output level stack leaving only the
     * current output level.
     */
    public function resetOutputLevel()
    {
        if (count($this->outputLevelStack) > 0) {
            $this->setOutputLevel(reset($this->outputLevelStack));
            $this->outputLevelStack = array();
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
    public function input()
    {
        return fgets($this->getStream('input'));
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
    private function getStream($type)
    {
        if (!isset($this->streams[$type])) {
            $this->streams[$type] = fopen($this->streamUrls[$type], $type == 'input' ? 'r' : 'w');
        }
        return $this->streams[$type];
    }

}
