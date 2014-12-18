<?php

class ArbitraryCommand implements \clearice\Command
{
    public function run($options)
    {
        \clearice\ClearIce::output(json_encode($options));
    }
}

