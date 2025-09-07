# CHANGELOG
## v3.2.2 - 2025-08-07
- Fixes broken parsing in newer PHP versions.

## v3.2.1 - 2022-12-17
- Removing deprecated php features to suppress warnings.

## v3.2.0 - 2021-02-01
### Added
- An option can now be assigned to more than one command. To do this, the `command` attribute of the option must be set to an array of all its supported commands. Setting the `command` attribute to a string causes it to behave as it previously did.

### Updated
- Help messages for command definitions are now required 

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
