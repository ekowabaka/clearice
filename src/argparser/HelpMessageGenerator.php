<?php

namespace clearice\argparser;


class HelpMessageGenerator
{
    public function generate($name, $options, $description, $footer)
    {
        return sprintf(
            "%s\n\n%s\n\nOptions:\n%s\n%s\n",
            wordwrap($description),
            $this->getUsageMessage($name),
            $this->getOptionHelpMessages($options),
            wordwrap($footer)
        );
    }

    public function getUsageMessage($name)
    {
        return sprintf("Usage:\n  %s [OPTIONS] ...", basename($name));
    }

    public function getOptionHelpMessages($options)
    {
        $message = "";
        foreach ($options as $option) {
            $message .= $this->formatOptionHelp($option) . "\n";
        }
        return $message;
    }

    /**
     * Formats the help line of a value which is accepted by an option. If a
     * value type is provided in the option, it is used if not it uses a generic
     * "VALUE" to show that an option can accept a value.
     *
     * @param array $option
     * @return string
     */
    private function formatValue($option)
    {
        if (isset($option['type'])) {
            return "=" . (isset($option['value']) ? $option['value'] : "VALUE");
        }
    }

    private function formatOptionHelp($option)
    {
        $optionHelp = array();
        $help = explode("\n", wordwrap($option['help'], 50));
        $argumentPart = $this->formatArgument($option);
        $optionHelp[] = $this->wrapHelp($argumentPart, $help);
        foreach ($help as $helpLine) {
            $optionHelp[] = str_repeat(' ', 29) . "$helpLine";
        }
        return implode("\n", $optionHelp);
    }

    private function formatArgument($option)
    {
        $valueHelp = $this->formatValue($option);
        $argumentHelp = "";
        if (isset($option['name']) && isset($option['short_name'])) {
            $argumentHelp = sprintf(
                "  %s, %-22s ", "-{$option['short_name']}", "--{$option['name']}$valueHelp"
            );
        } else if (isset($option['name'])) {
            $argumentHelp = sprintf(
                "  %-27s", "--{$option['name']}$valueHelp"
            );
        } else if (isset($option['short_name'])) {
            $argumentHelp = sprintf(
                "  %-27s", "-{$option['short_name']}"
            );
        }
        return $argumentHelp;
    }

    /**
     * Wraps the help message arround the argument by producing two different
     * columns. The argument is placed in the first column and the help message
     * is placed in the second column.
     *
     * @param string $argumentPart
     * @param string $help
     * @param integer $minSize
     * @return string
     */
    private function wrapHelp($argumentPart, &$help, $minSize = 29)
    {
        if (strlen($argumentPart) <= $minSize) {
            return $argumentPart . array_shift($help);
        } else {
            return $argumentPart;
        }
    }
}