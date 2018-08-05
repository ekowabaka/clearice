<?php

namespace clearice\utils;


class ProgramControl
{
    public function quit($status = 0)
    {
        exit($status);
    }
}