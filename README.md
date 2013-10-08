BsbDoctrineTranslator
=====================

WARNING! very much work in progress - do not use in production!

Set of tools to manage translation from a database.

## Installation

## Configuration

## Components

### DoctrineTranslatorLoader

Hooks in to the regular translator.

### LocalizedTemplatePathStackResolver


### Commandline Tool

#### Commands

The source scanner provides the ability to detect any translate(message, domain, locale) translatePlural(message, plural, number, domain, locale) invokations in PHP source.

scan-source accepts these arguments

	--locale 
	--domain 
	--file=path/to/file
	--kind=all|singular|plural defaults to all

To list detected messages
	
	scan-source list 

Export detected messages

	scan-source export > exported.txt
	
The comparison component provides the ability to compare message defined in source to the actual translation in the database

	Provides the ability to compare the message found in source to messages stored in database
	  
	compare untranslated
	
	
Features (some planned)

- Warn against missing printf tokens in plural messages
- Warn against conflicting singular and plural message (since these should never be the same)
- Find messages without a translation
- Remove messages defined in database but not detected in source
- Add messages defined in source but not present in database
- Exporting to various formats
- Change a translation
- Detect a translation invokation that has moved due to refactoring
- Detect a change of message refactoring
- Display the source context of a message

- partial & partialLoop
- 