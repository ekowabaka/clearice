Automated Help
--------------
Another feature ClearIce provides is the automatic generation of a help listing for your application. This means its possible for your users can type `myapp --help` or 
`myapp -h` and get a help listing. For cli apps which use command groups, the help system can also provide help messages for each command defined in the application. Although you'll have to provide some of the content for all the help magic to happen, the tool helps format and display your content in a very consistent form.


Auto Generating Help
--------------------
You can automatically generate a help option by calling the `ClearIce::addHelp()`
method. Once you have  provided help lines for all your options you pretty much 
have all you need.

With our example as shown above, if you should add `ClearIce::addHelp()` before
the `ClearIce::parse()` and execute:

    php wiki.php --help

or 

    php wiki.php -h

you should get:

    Commands:
    generate            generate a wiki
    serve               start an HTTP server to serve a wiki
    help                displays specific help for any of the given commands.
                        Usage: wiki.php help [command]

    Options:
      -v, --verbose              displays detailed information about everything
                                 that happens
      -h, --help                 shows this help message



You can add a description, usage information and footnotes by calling; 
`ClearIce::setDescription`, `ClearIce::setUsage` and `ClearIce::setFootnote`
respectively.

````php
ClearIce::setDescription("Simple Wiki version 1.0\nA sample or should I say dummy wiki app to help explain ClearIce. This app practically does nothing.");
ClearIce::setUsage("[command] [options]..");
ClearIce::setFootnote("Hope you had a nice time learning about ClearIce. We're pretty sure your cli apps would no longer be boring to work with.\n\nReport bugs to bugs@clearice.tld");
````

Now your help command (`php wiki.php -h` or `php wiki.php --help`) would generate 

    Simple Wiki version 1.0
    A sample or should I say dummy wiki app to help explain ClearIce. This app
    practically does nothing.

    Usage:
      wiki.php [command] [options]..

    Commands:
    generate            generate a wiki
    serve               start an HTTP server to serve a wiki
    help                displays specific help for any of the given commands.
                        Usage: wiki.php help [command]

    Options:
      -v, --verbose              displays detailed information about everything
                                 that happens
      -h, --help                 shows this help message

    Hope you had a nice time learning about ClearIce. We're pretty sure your
    cli apps would no longer be boring to work with.

    Report bugs to bugs@clearice.tld