Interactive I/O with ClearIce
=============================

[[_TOC_]]


Most console apps require some kind of interactivity, especially in cases where command line arguments are not passed. ClearIce makes it possible to perform interactive I/O. You can write output to the standard output (or any other output stream) with the capability of limiting verbosity. You are also able to read input from users interactively with the capablity
of validating the input.

All IO operations in ClearIce are performed through the `clearice\io\Io` class.

````php
use clearice\io\Io;
$io = new Io();
````

Producing Output
----------------
Clearice's `Io` class provides an `output()` method which writes text to an output
stream (standard output by default). For example to print the legendary "Hello World" to  screen you could just write:

````php
use clearice\io\Io;
$io = new Io();
$io->output("Hello World!");
````

The `output()` method takes a second parameter which specifies an output level. Output levels provide a means of limiting the verbosity of what the application writes to output stream. ClearIce has integer output levels, with a global output level set at anytime. Any output that's specified with an output level greater than the global output level is suppressed. Anything lower or equal to the global level is, however, displayed. The global output level can always be set with the `setOutputLevel` method. For example ...

````php
use clearice\io\Io;
$io = new Io();

// This shouldn't produce any output
$io->output("Hello World", Io::OUTPUT_LEVEL_2);

$io->setOutputLevel(Io::OUTPUT_LEVEL_2);

// This however should
$io->output("Hello World Again", Io::OUTPUT_LEVEL_2);
````

The `Io` contains four output level constants: `Io::OUTPUT_LEVEL_0`, `Io::OUTPUT_LEVEL_1`, `Io::OUTPUT_LEVEL_2`, and `Io::OUTPUT_LEVEL_3`, having inter values of 0 through 3 respectively. By convention `Io::OUTPUT_LEVEL_0` should not be assigned to any calls so it could be used as a mute level. Once the global output level is set to 0, no $io->output() calls should write output, provided this convention is followed. The other three levels could be used as low, medium and high respectively. It is possible to define more output levels for your application if you needed.

Apart from the output stream, ClearIce also allows writing to the error stream through the `error()` method on the `Io` class. This method behaves exactly as the output method except that it writes to an error stream (standard error by default).

### Using the output level stack
To help you manage the output levels effectively, ClearICE provides an output stack. The stack could be accessed through the `pushOutputLevel` and `popOutputLevel` methods on the `Io` class. Anytime the `pushOutputLevel` method is called, the current output level is set to the value that was pushed unto the stack. When the outputLevel is popped, the output level reverts to the output level that existed before the last push occurred. This way you do not have to keep track of the existing output level if the need exists to temporarily switch output levels to enforce some output.

It is also perfectly safe to mix the stack methods with the already existing `setOutputLevel` method. Anytime the stack is built, the current value set through the setOutputLevel is considered.

Consuming Input
---------------
The `Io` class also provides a `input()` method which reads a line of text from an input stream (standard input by default) and returns it. For validated input, `Io` contains the `getResponse()` method which reads the input and validates it. To ask the user a question you could use ...

````php
use clearice\io\Io;
$io = new Io();
$name = $io->getResponse('What is your name');
````

This would present the user with a prompt ...

    What is your name []: 
    
If you want to provide a default value which should be returned in case the user
does not supply a response you could use.

````php
$name = $io->getResponse('What is your name', ['default' => 'No Name']);
````
Which would then produce the prompt ...

    What is your name [No Name]: 
    
Supposing we want a follow up question which would find out where the user wants
to go we could use ...

````php
$name = $io->getResponse('What is your name', ['default' => 'No Name']);
$direction = $io->getResponse("Okay $name, where do you want to go", ['required' => true]);
````

Note that the second call to `getResponse` adds a `required` parameter. Assuming
we just hit the enter key twice when we execute the above script, we should end
up with an output which lools like ...

    What is your name [No Name]: 
    Okay No Name, where do you want to go []: 
    A value is required.
    Okay No Name, where do you want to go []: 
    
In cases where we want to give the user options to select from, we could go 
ahead and use ...

````php
$name = $io->getResponse('What is your name', ['default' => 'No Name']);
$direction = $io->getResponse("Okay $name, where do you want to go", 
    [
        'required' => true,
        'answers' => array('north', 'south', 'east', 'west')
    ]
);
````

Executing this and hitting enter twice while typing an invalid answer the third
time would give the following output

    What is your name [No Name]: 
    Okay No Name, where do you want to go (north/south/east/west) []: 
    A value is required.
    Okay No Name, where do you want to go (north/south/east/west) []: home
    Please provide a valid answer.
    Okay No Name, where do you want to go (north/south/east/west) []: 


    
