<?php

class OptionedCommand implements \clearice\Command
{
    public static function getCommandOptions()
    {
        return [
            'command' => 'optioned',
            'help' => 'some optioned command',
            'options' => [
                [
                    'short' => 'i',
                    'long' => 'input',
                    'has_value' => true,
                    'help' => "specifies where the input files for the wiki are found."
                ],
                [
                    'short' => 'o',
                    'long' => 'output',
                    'has_value' => true,
                    "help" => "specifies where the wiki should be written to"
                ],
                [
                    'short' => 'v',
                    'long' => 'verbose',
                    "help" => "displays detailed information about everything that happens"
                ],
                [
                    'short' => 'x',
                    'long' => 'create-default-index',
                    'has_value' => false,
                    "help" => "creates a default index page which lists all the wiki pages in a sorted order"
                ]
            ]
        ];
    }
    
    public function run($options)
    {
        \clearice\ClearIce::output(json_encode($options));
    }
}
