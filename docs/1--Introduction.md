Introduction
============

ClearIce is a library which aims to make it easy to deal with certain routine 
operations ecountered when building CLI apps. ClearIce currently provides 
argument parsing and console I/O features. 

The ClearIce argument parser parses arguments in a style similar to what you 
would find in most GNU style applications. It provides a feature to define
commands under which you could group the various options your app receives from 
the shell. One cool feature of the ClearIce argument parser is the automatic
help generator which formats and generates help texts for your application.

The ClearIce I/O component allows you to read and write to streams (standard
output, standard input and standard error by default). It provides a means
to control verbosity so end users of your app can determine how much
information they want to receive from your app. The I/O component also has a 
few functions to help you perform interactive console sessions.

Goals
-----
ClearIce was built with the following goals in mind:

 - To be easy to use (single class interface).
 - PHP Framework agnostic.
 - Easy to install (leverages composer).

Installation
------------
The best and most preferred way to install ClearIce is through composer. You
can require the [ekowabaka/clearice](http://packagist.org/packages/ekowabaka/clearice)
dependency in your application to get the ClearIce library.