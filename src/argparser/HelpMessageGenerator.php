<?php

namespace clearice\argparser;


class HelpMessageGenerator
{
    public function generate($name, $command, $options, $description, $footer)
    {
        if($command) {
            return wordwrap($description) . "\n\n"
                . $this->getUsageMessage($name, $options, $command)
                . $this->getOptionHelpMessages($options['options'], $command)
                . $this->getOptionHelpMessages($options['options'])
                . wordwrap($footer) . "\n";
        } else {
            return wordwrap($description) . "\n\n"
                . $this->getUsageMessage($name, $options, $command)
                . $this->getCommandsMessage($options['commands'])
                . $this->getOptionHelpMessages($options['options'])
                . wordwrap($footer) . "\n";
        }
    }

    private function getCommandsMessage($commands)
    {
        if(count($commands)) {
            $commandsHelp = array('Commands:');
            foreach ($commands as $command)
            {
                $commandsHelp[] = implode("\n", $this->formatCommandHelp($command));
            }
            $commandsHelp[] = '';
            return implode("\n", $commandsHelp) . "\n";
        }
        return '';
    }

    private function formatCommandHelp($command)
    {
        $commandHelp = array();
        $help = explode("\n", wordwrap($command['help'], 59));
        $commandHelp[] = $this->wrapHelp(sprintf("% -20s", $command['name']), $help, 20);
        foreach($help as $helpLine)
        {
            $commandHelp[] = str_repeat(' ', 20) . $helpLine;
        }
        return $commandHelp;
    }

    private function getUsageMessage($name, $options, $command = '')
    {
        return sprintf(
            "Usage:\n  %s %s%s[OPTIONS] ...\n\n", basename($name),
            count($options['commands']) > 0 && $command == '' ? "[COMMAND] " : "",
            $command != "" ? "$command ": ""
        );
    }

    private function getOptionHelpMessages($options, $command = '')
    {
        $message = $command == '' ? "Options:\n" : "Options for $command command:\n";
        foreach ($options as $option) {
            if($option['command'] !== $command) {
                continue;
            }
            $message .= $this->formatOptionHelp($option) . "\n";
        }
        return "$message\n";
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
     * @param array $help
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