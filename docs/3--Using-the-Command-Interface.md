Using the Command Interface
===========================
ClearIce ships with a `CommandInterface` which exposes a single method, `run`.
Using the command interface provides a much more structured way of implementing the 
clearice library. When using this interface, clearice instantiates your class
when its associated command is invoked and calls the run method with any extra
parameters that were passed. To make clearice aware of the possible options of 
your command class you would use an optional static `getCommandOptions()`
method. This method returns an array which represents the options of a given
implementation of the `CommandInterface`.

Creating a CommandInterface implementation
------------------------------------------
As stated earlier, a class that implements the `CommandInterface` automatically
becomes a clearice command. Assuming we want to convert the generate command
from our wiki application, we could start out with a base class as follows:

````php
class GenerateCommand implements \clearice\CommandInterface
{
    public function run($options){
        //.. put wiki generation code here
    }
}
````

In our application's entry script we could go ahead and add:

````php
ClearIce::addCommand('GenerateCommand');
````

We now have our generate command registered. However, the generate command
requires certain options so we will go ahead and add a static 
`getCommandOptions()` method which will return all the options. One reason
why this method was kept static was to prevent the instantiation of all
command classes during command line parsing.

````php
class GenerateCommand implements \clearice\CommandInterface
{
    public function run($options){
        //.. put wiki generation code here
    }

    public static function getCommandOptions()
    {
        return [
            'command' => 'generate',
            'help' => 'generates your wiki',
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
}
````

In the example above, we can clearly see how the getCommandOptions() method
can be used to expose options of a command. This approach is analogous to the
previous approach of passing the options directly to clearice. 
