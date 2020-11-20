# CHANGELOG

## v3.1.1 - 2020-11-19
### Fixed
- Default values for options without commands are now returned when a command is passed a an argument.

### Updated
- Improved documentation and user guide

## v3.1.0 - 2020-05-28
### Added
- An extensive amount of documentation, explaining how ClearIce works.
- An exit callback function so any attempt by ClearIce to terminate the script can be intercepted.
- Interfaces to define help generators and validators. 

### Removed
- The ProgramControl class.

### Updated
- Dependencies and unit test frameworks.


## v3.0.2 - 2019-03-24
### Added
- An `__executed` key to the output of the parser to show the binary executed on the shell.

## v3.0.1 - 2018-10-14
### Fixed
- Arguments not attached to any command can be called from all commands.

## v3.0.0 - 2018-08-04
- Third major release and start of Changelog 👍
