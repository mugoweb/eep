#eep - ease eZPublish
> eep is a command line tool to support developers using eZPublish.

## Installation
- [Linux](installation.md#installation---linux)
- [OS X](installation.md#installation---os-x)
- [Windows](installation.md#installation---windows)

## Getting started
To set the ezpublish instance used with eep and list some content classes for future modifications:
```sh
$ cd <ezpublish root folder>
$ eep use ezroot .
$ eep list contentclasses
```
To create a new content object and fill it with random data:
```sh
$ eep use contentclass <class identifier>
$ eep use contentnode <parent node id>
$ eep create content anObject
```
For help:
```sh
$ eep help
$ eep <module> help
$ eep help <module>
```
Shortcuts:  
`$ eep contentclass article` becomes `$ eep cc article`
```sh
  at => attribute         (module)
  cc => contentclass      (module)
  co => contentobject     (module)
  cn => contentnode       (module)
  kb => knowledgebase     (module)
 ccg => contentclassgroup (module)
 ats => attributes        (method) e.g. eep list ats
 ccs => contentclasses    (method) e.g. eep list ccs
 cos => contentobjects    (method) e.g. eep list cos
 cns => contentnodes      (method) e.g. eep list cns
coid => contentobjectid   (method) e.g. eep at coid
```

## Modules
- [attribute](modules_attributes.md#modules---attribute)
- [contentclass](modules_contentclass.md#modules---contentclass)
- [contentclassgroup](modules_contentclassgroup.md#modules---contentclassgroup)
- [contentnode](modules_contentnode.md#modules---contentnode)
- [contentobject](modules_contentobject.md#modules---contentobject)
- [create](modules_create.md#modules---create)
- [crondaemon](modules_crondaemon.md#modules---crondaemon)
- [ezfind](modules_ezfind.md#modules---ezfind)
- [ezflow](modules_ezflow.md#modules---ezflow)
- [help](modules_help.md#modules---help)
- [knowledgebase](modules_knowledgebase.md#modules---knowledgebase)
- [list](modules_list.md#modules---list)
- [section](modules_section.md#modules---section)
- [trash](modules_trash.md#modules---trash)
- [use](modules_use.md#modules---use)

## Core libs
- [AttributeFunctions](core_attribute_functions.md#core---attributefunctions)
- [eepCache](core_eep_cache.md#core---eepcache)
- [eepHelpers](core_eep_helpers.md#core---eephelpers)
- [eepLog](core_eep_log.md#core---eeplog)
- [eepValidate](core_eep_validate.md#core---eepvalidate)

## Extending eep
- [Updating bash completion](#extending---bash-completion)
- [Creating a new module](#extending---new-module)
- [Creating a new module method](#extending---new-module-method)

