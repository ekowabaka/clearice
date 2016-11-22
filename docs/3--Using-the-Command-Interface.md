Using the Command Interface
===========================
ClearIce ships with a `CommandInterface` which exposes a single method, `run`.
Using the command interface provides a much more structured way of implementing the 
clearice library. When using this interface, clearice instantiates your class
when its associated command is invoked and calls the run method with any extra
parameters that were passed. To make clearice aware of the possible options of 
your command class you would use an optional static `getCommandOptions()`
method.