# Bartleby's Flexions 

Flexions is a simple set of scripts (written in basic PHP) to perform code generation.
Flexions intents to be an easy to use code generator.
The simpliest way to understand how it works is to check our samples in the samples folder.  

Current version is 2.0.


## Flexions life cycle

The flexions.script /src/flexions/Core/flexions.script.php Interprets and validates the command line and then runs through 3 phases : pre-processing / processing / post-processing

### 1- pre-processing : ( you can have multiple pre-processors )
1. can proceed to preparation (pre-generation, data set building per introspection, etc....)
2. loads the "descriptor" and transform them to an Hypotypose instance

#### We create aliases to be used in the templates :
- $h refers to the singleton Hypotypose::instance()
- $d refers whithin the loop to the current focus for example : $d=$actions[19] if the loop runs on the 19th actions.
- $f refers to the current Flexed instance

### 2- processing (usually a triple loop)  :
1. Per entity loop (entity ) enumerates { $entities }
2. Action loop (operations, command) enumerates { $actions }
3. One stop loop (api , http client , shared headers) runs once  per $project

IMPORTANT if the package contains a loops.php file the standard flexion loop is not used.

### 3- post-processing : (you can have multiple post-processors)
Each loop store a collection of Flexed instances  
1. Can use the "Flexed" instances to generate sub-Flexed (for example an header file for all the generated files)
2. Serializes the "Flexed" to sources files to the destination.
3. Perform any other post processing action (notification, push, ....)

## Usage of the Php command line : 
  
```shell
php  -f ${cmdPath} source=${source} destination=${destination} descriptor=${descriptor} templates=${templates}  preProcessors=${pre} postProcessors=${post}
```
### But most of the time you will prefer to create a .sh wrapper 

- source:the generation source folder path
- descriptor:the descriptor file name
- templates:templates relative path separated by commas for advanced cibled generation OR  "* " for recursive generation  for simple hierarchies (default value =="*")
- destination:destination path (default value is =="out/standard/")
- preProcessors:the pre-processors separated by commas
- postProcessors:the post-processors separated by commas


## About templates

To determinate if a template should be used in a loop we check if there is an occurence of the loopname in the path.
To include a template in the "project" loop put it in : templates/project/myTemplate.php


### Prototypes Template

Sample of a prototype template.

```php
include FLEXIONS_SOURCE_DIR.'/Shared.php';
// Define the globals before calling include
$filemame="/LoginUser.swift";
$package="xOS/operations/";
$prototypePath=FLEXIONS_MODULES_DIR.'Bartleby/prototypes/swift/auth/LoginUser.swift';
include FLEXIONS_MODULES_DIR . 'Bartleby/templates/filePrototypeTemplate.php';
```



