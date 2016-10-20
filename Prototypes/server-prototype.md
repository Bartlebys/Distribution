# App prototype

This folder contains the official app prototype for Bartleby 1.0. A prototype is a template with default values.

This document lists the Constants that are used to configure a fresh new application.
Those constants are reserved key-work for any App prototype. App prototype developers should insure they do not miss use those strings. During the creation process to strings may be replaced by *bartleby's CLI*.

\* Conventions: "**Mandatory Keys**" are bold while "*Optionnal advanced Keys*" are not 

## App Constants 

KEYS  | E.G. | EXPLANATIONS
------------- | ------------- | ------------------------
**AP\_xOS\_AEP** | /Users/bpds/Documents/Swift-AppX/Shared/_generated | This is the xOS export path. "xOS" stands for iOS / OSX / tvOS / wathOS
**AP\_xOS\_BCEP** | www |The web server public root folder. It is a relative path
**AP\_SK** | adde3-333DD-DDE80-123DE-11x6-Z3934eEJF!-= | Salt 32 characters min used to encrypt-decrypt the values of the Cookies. This key is never shared.
**AP\_PSS** | xxxx-64744-edE80-49!4DE-1377486-Zzr8D99Ee | Shared Salt 32 characters min this salt is shared with the client libs.
**AP\_MDN** | MyProjectDB | The MongoDB name.
*AP\_PRF*| /Users/bpds/Documents/Swift-AppX/Bartleby/BartlebyKit/BartlebySources/_generated | If you build BartlebyKit you need to define Bartleby's commons sources export path. You can alternatively use a compiled version of the framework.

## BartlebySync Constants 


KEYS  | E.G. | EXPLANATIONS
------------- | ------------- | ------------------------
**AP\_BS\_SSK\_{STAGE}** | default-secret-key | Sync secret Key, to create the data system folder (and future extensions)
**AP\_BS\_RH\_{STAGE}** | http://repository.local:80/ | the repository host
**AP\_BS\_WP** | files | the repository relative writing path