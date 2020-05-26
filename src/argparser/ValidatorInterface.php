<?php


namespace clearice\argparser;


interface ValidatorInterface
{
    public function validateArguments($options, $parsed);
    public function validateOption($option, $commands);
    public function validateCommand($command, $commands);
}