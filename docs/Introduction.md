Introduction
============

ClearIce aims to make it easy for you to deal with certain routine operations usually encountered when building CLI apps, by providing argument parsing and console I/O tools. 

The argument parser, accepts arguments in a style, similar to what you would find in most GNU  applications &mdash; single dashes preceeding single character arguments, and double dashes for longer ones, with the option of making longer options synonymous to shorter ones. For applications that operate in multiple modes, you are given the ability to define commands under which you could group the options for each individual command. Finally, no argument parser worth its salt would ship without support for generating help messages, and ClearIce is no exception.

The I/O component in ClearIce allows you to read and write to streams (standard output, standard input and standard error by default). It provides a means to control verbosity, so end users of your app can determine how much information they want to receive from your app. Most importantly, the I/O component makes it a breeze for you to collect and validate user input. 

Goals
-----
ClearIce was built with the following goals in mind:

 - To be easy to use.
 - PHP Framework agnostic.
 - Easy to install.

Installation
------------
The best and most preferred way to install ClearIce is through composer. You can require the [ekowabaka/clearice](http://packagist.org/packages/ekowabaka/clearice) dependency in your application to get the ClearIce library.

