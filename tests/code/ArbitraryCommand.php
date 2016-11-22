<?php

class ArbitraryCommand implements \clearice\CommandInterface
{
    public function run($options)
    {
        \clearice\ClearIce::output(json_encode($options));
    }

    public function getCommandOptions() {}
}

