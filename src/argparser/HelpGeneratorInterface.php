<?php


namespace clearice\argparser;


interface HelpGeneratorInterface
{
    public function generate($name, $command, $options, $description, $footer);
}