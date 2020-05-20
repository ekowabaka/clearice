Interactive I/O with ClearIce
=============================
Most console apps require some kind of interactivity, especially in cases where
command line arguments are not passed. ClearIce contains a collection of
methods which makes it possible to perform interactive I/O. ClearIce allows
apps to write output to the standard output with the capability of limiting verbosity. 
ClearIce also allows reading input from users interactively with the capablity
of validating the input.

Producing Output
----------------
Clearice provides the `ClearIce::output()` method which writes text to an output
stream (standard output by default). For example to print the legendary "Hello World" to 
screen you could just write:

````php
ClearIce::output('Hello World');
````

The `ClearIce::output()` method takes a second parameter which specifies an 
output level. Output levels provide the library with a means of limiting the 
verbosity of what the application writes to output stream. The clearice library 
always has a global output level which is (`ClearIce::OUTPUT_LEVEL_1`) by default.
Whenever the output method is called with an output level paramter, the content
would only be written to the output stream when the output level value of the call
is less than or equal to the global output level. The global output level can
always be set with the `ClearIce::setOutputLevel` method. For example ...

````php
// This shouldn't produce any output
ClearIce::output("Hello World", ClearIce::OUTPUT_LEVEL_2);

ClearIce::setOutputLevel(ClearIce::OUTPUT_LEVEL_2);

// This however should
ClearIce::output("Hello World Again", ClearIce::OUTPUT_LEVEL_2);
````

The ClearIce library ships with four output level constants (`ClearIce::OUTPUT_LEVEL_0`,
`ClearIce::OUTPUT_LEVEL_1`, `ClearIce::OUTPUT_LEVEL_2` and `ClearIce::OUTPUT_LEVEL_3`).
By convention `ClearIce::OUTPUT_LEVEL_0` should not be assigned to any calls so
it could be used as a mute level. This means that once the global output level
is set to 0, no ClearIce::output() calls should write output (given that this
convention is followed). The other three levels could be used as low, medium and
high respectively. It is possible to define more output levels for your application
if needed.

Apart from the output stream, ClearIce also allows writing to the error stream
through the `ClearIce::error()` method. This method behaves exactly as the output
method except that it writes to an error stream (standard error by default).

### Using the output level stack
ClearIce ships with an output level stack which allows you to manage output 
levels more effectively. The stack could be accessed through the `ClearIce::pushOutputLevel`
and `ClearIce::popOutputLevel` methods. Anytime the `ClearIce::pushOutputLevel` method is
called, the current output level is set to the value that was pushed unto the stack.
When the outputLevel is popped, the output level reverts to the output level
that existed before the last push occurred. This way the developer does not have
to keep track of the existing output level if the need exists to temporarily 
switch output levels.

It is also perfectly safe to mix the stack methods with the already existing
`ClearIce::setOutputLevel` method. Anytime the stack is built, the current
value set through the setOutputLevel is considered.

Consuming Input
---------------
ClearIce also provides a `ClearIce::input()` method which reads a line of text 
from an input stream (standard input by default) and returns it. For validation
purposes, ClearIce contains the `ClearIce::getResponse()` method which reads
the input and validates it. To ask the user a question you could use ...

````php
$name = ClearIce::getResponse('What is your name');
````

This would present the user with a prompt ...

    What is your name []: 
    
If you want to provide a default value which should be returned in case the user
does not supply a response you could use.

````php
$name = ClearIce::getResponse('What is your name', array('default' => 'No Name'));
````
Which would then produce the prompt ...

    What is your name [No Name]: 
    
Supposing we want a follow up question which would find out where the user wants
to go we could use ...

````php
$name = ClearIce::getResponse('What is your name', array('default' => 'No Name'));
$direction = ClearIce::getResponse("Okay $name, where do you want to go", array('required' => true));
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
$name = ClearIce::getResponse('What is your name', array('default' => 'No Name'));
$direction = ClearIce::getResponse("Okay $name, where do you want to go", 
    array(
        'required' => true,
        'answers' => array('north', 'south', 'east', 'west')
    )
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


    
