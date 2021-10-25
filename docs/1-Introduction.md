---
title: Introduction
---
Introduction
============

[[_TOC_]]


ClearIce aims to make it easy to deal with the routine operations that are usually encountered when building CLI apps. Simply, ClearIce provides command line argument parsing and console I/O tools. 

The argument parser in ClearICE accepts arguments in a style similar to what you would find in most GNU CLI applications &mdash; single dashes preceding single character arguments, and double dashes for longer ones. In such a configuration, shorter options are typically used as shortcuts for longer ones. 

For applications that operate in multiple modes, such as the cases where different actions can be performed with the same command, ClearIce gives you the ability to define commands under which you can group options for your application's different modes. Finally, no argument parser worth its salt would ship without support for generating help messages, and ClearIce is no exception.

The other component in ClearIce, the parts for console I/O, allow you to read and write to streams (standard output, standard input and standard error by default). Through the I/O system verbosity of output can be controlled, so end users of your app can determine how much information they want to receive from your app. Most importantly, the I/O component makes it a lot easier for you to collect and validate user input. 

Goals
-----
ClearIce was built with the following goals in mind:

 - Easy of use.
 - PHP Framework agnostic.
 - Easy to install.

Installation
------------
The preferred way to install ClearIce is through composer. You can require the [ekowabaka/clearice](http://packagist.org/packages/ekowabaka/clearice) dependency in your application.

If for some reason you can't use composer, you can download the ClearIce source files and include them directly in your scripts.

