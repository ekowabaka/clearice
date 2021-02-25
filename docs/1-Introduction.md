---
title: Introduction
---
Introduction
============

[[_TOC_]]


ClearIce aims to make it easy for you to deal with those routine operations usually encountered when building CLI apps, by providing argument parsing and console I/O tools. 

The argument parser in ClearICE accepts arguments in a style similar to what you would find in most GNU  applications &mdash; single dashes preceding single character arguments, and double dashes for longer ones, where shorter options are usually shortcuts to longer ones. For applications that operate in multiple modes, ClearIce also gives you the ability to define commands under which you can group options for your application's different modes. Finally, no argument parser worth its salt would ship without support for generating help messages, and ClearIce is no exception.

The other component in ClearIce, the I/O component, allows you to read and write to streams (standard output, standard input and standard error by default). It provides a means to control verbosity, so end users of your app can determine how much information they want to receive from your app. Most importantly, the I/O component makes it a lot easier for you to collect and validate user input. 

Goals
-----
ClearIce was built with the following goals in mind:

 - Easy of use.
 - PHP Framework agnostic.
 - Easy to install.

Installation
------------
The preferred way to install ClearIce is through composer. You can require the [ekowabaka/clearice](http://packagist.org/packages/ekowabaka/clearice) dependency in your application.

If for some reason you can't use composer, you can download the ClearIce source files and include them in your scripts.

