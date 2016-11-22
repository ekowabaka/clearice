<?php
namespace clearice;

/**
 * An interface to be implemented by classes which are to be auto instantiated
 * by Clearice.
 */
interface CommandInterface
{
    /**
     * The run method is called by clearice when the command to which a
     * particular implementation is assigned is executed.
     * This method receives all the options that clearice parsed through its
     * $options parameter.
     * @param array $options The options that were parsed by clearice.
     */
    public function run($options);
}
