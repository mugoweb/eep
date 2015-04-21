#eep - ease eZPublish
> eep is a command line tool to support developers using eZPublish.

## Installation
- [Linux](#installation---linux)
- [OS X](#installation---os-x)
- [Windows](#installation---windows)

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

## Modules
- [attribute](#modules---attribute)
- [contentclass](#modules---contentclass)
- [contentclassgroup](#modules---contentclassgroup)
- [contentnode](#modules---contentnode)
- [contentobject](#modules---contentobject)
- [create](#modules---create)
- [crondaemon](#modules---crondaemon)
- [ezfind](#modules---ezfind)
- [ezflow](#modules---ezflow)
- [help](#modules---help)
- [knowledgebase](#modules---knowledgebase)
- [list](#modules---list)
- [section](#modules---section)
- [trash](#modules---trash)
- [use](#modules---use)

## Core libs
- [AttributeFunctions](#core---attributefunctions)
- [eepCache](#core---eepcache)
- [eepHelpers](#core---eephelpers)
- [eepLog](#core---eeplog)
- [eepValidate](#core---eepvalidate)

## Extending eep
- [Updating bash completion](#extending---bash-completion)
- [Creating a new module](#extending---new-module)
- [Creating a new module method](#extending---new-module-method)

#eep installation

## Installation - Linux
1. Extract to somewhere, like ```$ /home/dfp/eep```
2. Make the controller executable
```sh 
$ chmod +x eep.php
```
3. Create a globally available link, like,
```sh 
$ su
$ cd /usr/bin
$ ln -s -T /home/dfp/eep/eep.php eep
```
4. edit eepSetting.php to update the path to the cache file to somewhere that is writable on your system, like ```$ /var/tmp/```


### Command line completion
On linuxes you can set up bash completion (which is highly convenient) by:
```
$ sudo ln -s /home/dfp/eep/bash_completion/eep /etc/bash_completion.d/eep
```
Note that on CentOS, you might have to:
```sh
$ sudo yum install bash-completion
```

In order to insert the eep commandline completion script into the current bash session, you have to either ```$ . /etc/bash_completion``` or start a new shell (like on CentOS).

## Installation - OS X

### Manual:
1. Create a bash completion directory and symlink it.
```sh
$ sudo mdkir /etc/bash_completion.d
$ sudo ln -s /your_path_to_eep/bash_completion/eep /etc/bash_completion.d/eep
```

2. Then add the following to your ```~/.bash_profile```, ```~/.bashrc``` etc. 
```sh
for f in /etc/bash_completion.d/*; do source $f; done
```
Any file in that location will now be sourced for bash use.
3. Start a new shell.

### Via Homebrew / Mac Ports
1. See [http://superuser.com/questions/288438/bash-completion-for-commands-in-mac-os](http://superuser.com/questions/288438/bash-completion-for-commands-in-mac-os)
2. Then create the symbolic link to ```/your_path_to_eep/bash_completion/eep``` in the installation specific completion folder.
3. Start a new shell

## Installation - Windows
1. Extract to somewhere, like ```C:\wamp\scripts\eep```
2. Make the controller executable: 
```sh
icacls C:\wamp\scripts\eep\eep.php /T /Q /C /RESET
```
3. Create a globally available link by adding eep.php to the ```PATH``` variable

### Installation note
You can override the settings by copying ```.../eep/eepSettings.php``` into your home folder and editing it. You may have to keep it uptodate with new versions, as these settings change.

#Modules - attribute
> The attribute module provides method to manipulate content object & content class attributes.

- [delete](#delete)
- [newattributexml](#newattributexml)
- [migrate](#migrate)
- [update](#update)
- [fromstring](#fromstring)
- [tostring](#tostring)
- [setfield](#setfield)
- [info](#info)
- [createalias](#createalias)

## delete
Deletes an attribute from a content class and it's content objects.
```sh
$ eep attribute delete <class identifier> <attribute identifier>
```

## newattributexml
Displays XML that can be edited and used for import.
```sh
$ eep attribute newattributexml
```

## migrate
Copies data from one content object attribute to another within a content class.
- Currently supported are ```rot13``` for testing and ```time2integer``` and ```trim``` and ```date2ts```

```sh
$ eep attribute migrate <class identifier> <src attribute> <conversion> <dest attribute>
```

## update
Updates content class and content objects with new attribute; will resume after a partial update.
```sh
$ eep attribute update <class identifier> <path to newattributexml file>
```

## fromstring
Calls FromString() on the content object's attribute.
```sh
$ eep attribute fromstring <content object id> <attribute identifier> <new value>
```

## tostring
Calls ToString() on the content object's attribute.
```sh
$ eep attribute tostring <content object id> <attribute identifier>
```

## setfield
Directly sets one of the content class attribute fields (e.g. ```data_int```, ```data_text1``` etc.)
```sh
$ eep attribute setfield <class identifier> <attributename> <fieldname> <fieldvalue>
```

## info
Displays all content class attribute fields (e.g. ```data_int```, ```data_text1``` etc.)
```sh
$ eep attribute info <class identifier> <attributename> <fieldname>
```

## createalias
Create a given alias manually for a given content object image attribute.
```sh
$ eep attribute createalias <content object id> <attribute identifier> <alias name>
```

#Modules - contentclass
> The contentclass module provides methods to manipulate content classes.

- [createclass](#createclass)
- [deleteclass](#deleteclass)
- [listattributes](#listattributes)
- [setclassobjectidentifier](#setclassobjectidentifier)
- [setiscontainer](#setiscontainer)
- [fetchallinstances](#fetchallinstances)
- [appendtogroup](#appendtogroup)
- [removefromgroup](#removefromgroup)

## createclass
Creates a stub content class with an automatic content class identifier and default string for object-naming; uses the "admin" user to create the class; returns the class identifier so that attributes can then be added and the default naming be updated.
```sh
$ eep createclass <display name> <content class group identifier>
```

## deleteclass
Deletes all the instances of a class, and then deletes the class itself.
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep contentclass deleteclass
or
$ eep use ezroot <path>
$ eep contentclass deleteclass <class identifier>
```

## listattributes
Lists all class attributes.
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep contentclass listattributes
```

## setclassobjectidentifier
Sets the string used to name instances of the class, uses the same syntax as in the admin UI.
```sh
$ eep contentclass setclassobjectidentifier <class identifier> <object naming string or pattern>
```

## setiscontainer
Sets or unsets the 'is container' flag on the class.
```sh
$ eep contentclass setiscontainer <class identifier> <0|1>
```

## fetchallinstances
Fetches all instances of a contentclass.
_Note that this supports limit and offset parameters._
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep contentclass fetchallinstances
or
$ eep contentclass fetchallinstances <content class identifier>
```

## appendtogroup
Adds a contentclass to a contentclass group.
```sh
$ eep use ezroot <path>
$ eep contentclass appendtogroup <content class identifier> <group identifier>
```

## removefromgroup
Removes a contentclass from a contentclass group.
```sh
$ eep use ezroot <path>
$ eep contentclass removefromgroup <content class identifier> <group identifier>
```

# Modules - contentclassgroup
> The contentclassgroup module provides methods to manipulate content class groups.

- [creategroup](#creategroup)
- [deletegroup](#deletegroup)
- [renamegroup](#renamegroup)
- [fetchall](#fetchall)

## creategroup
Creates a new content class group.
```sh
$ eep use ezroot <path>
$ eep contentclassgroup creategroup <group identifier>
```

## deletegroup
Deletes the specified content class group
```sh
$ eep use ezroot <path>
$ eep contentclassgroup deletegroup <group identifier>
```

## renamegroup
Renames a content class group.
```sh
$ eep use ezroot <path>
$ eep contentclassgroup renamegroup <group identifier from> <group identifier to>
```

## fetchall
Displays all content class groups.
```sh
$ eep use ezroot <path>
$ eep contentclassgroup fetchall
```

# Modules - contentnode
> The contentnode module provides methods to manipulate content nodes.

- [clearsubtreecache](#clearsubtreecache)
- [contentobject](#contentobject)
- [info](#info)
- [location](#location)
- [find](#find)
- [find_by_attribute] not implemented searchForNodes_byAttribute()
- [deletesubtree](#deletesubtree)
- [move](#move)
- [setsortorder](#setsortorder)

## clearsubtreecache
Clears the content object cache for items in a subtree (if required).
```sh
$ eep clearsubtreecache <node id>
```

## contentobject
Converts a content node id to a content object id.
```sh
$ eep use ezroot <path>
$ eep contentnode contentobject <content node id>
or
$ eep use ezroot <path>
$ eep use contentnode <content node id>
$ eep contentnode contentobject
```

## info
Displays content node information.
```sh
$ eep use ezroot <path>
$ eep use contentnode <node id>
$ eep contentnode info
or
$ eep use ezroot <path>
$ eep contentnode info <node id>
```

## location
Creates a content object at an additional location.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentnode location <new parent node id>
```

## find
Finds a node of a given content class by parent node id.
- supports ```--limit=N``` and/or ```--offset=M```

```sh
$ eep use ezroot <path>
$ eep cn find <content class> <parent node id> <search string>
```

## deletesubtree
Deletes a subtree of nodes.
- is hardcoded to use ```user 14 (admin)``` to do the deletions
- supports ```--limit=N``` to override the sanity check limit (0 means no-limit)

```sh
$ eep use ezroot <path>
$ eep contentnode deletesubtree <subtree node id>
or
$ eep use contentnode <subtree node id>
$ eep contentnode deletesubtree
```

## move
Moves a node to be a child at the new location.
```sh
$ eep use ezroot <path>
$ eep use contentnode <node id>
$ eep contentnode move <new parent node id>
or
$ eep contentnode move <node id> <new parent node id>
```

## setsortorder
Sets the sort order for children of a node.
(You may have to republish the object to make the change visible.)   
The available orderings are:
- PATH
- PUBLISHED
- MODIFIED
- SECTION
- DEPTH
- CLASS_IDENTIFIER
- CLASS_NAME
- PRIORITY
- NAME
- MODIFIED_SUBNODE
- NODE_ID
- CONTENTOBJECT_ID

The available directions are:
- DESC
- ASC
```sh
$ eep contentnode setsortorder <node id> <sort ordering> <sort direction>
```

# Modules - contentobject
> The contentobject module provides methods to manipulate content objects.

- [clearcache](#clearcache)
- [info](#info)
- [datamap](#datamap)
- [delete](#delete)
- [related](#related)
- [reverserelated](#reverserelated)
- [contentnode](#contentnode)
- [republish](#republish)
- [sitemapxml](#sitemapxml)
- [deleteversions](#deleteversions)
- [fetchbyremoteid](#fetchbyremoteid)

## clearcache
Clears the content cache for given content object.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject clearcache
or
$ eep contentobject clearcache <object id>
```

## info
Displays some information about a content object.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject info
or
$ eep contentobject info <object id>
```

## datamap
Displays most of the content object datamap.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject datamap
or
$ eep contentobject datamap <object id>
```

## delete
Deletes a content object and it's children.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject delete
or
$ eep contentobject delete <object id>
```

## related
Displays a list of related content objects.   
- supports use of ```--limit=N``` and ```--offset=M```

```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject related
or
$ eep contentobject related <object id>
```

## reverserelated
Displays a list of reverse related content objects.
- supports use of ```--limit=N``` and ```--offset=M```

```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject reverserelated
or
$ eep contentobject reverserelated <object id>
```

## contentnode
Converts a content object id into a content node id.
```sh
$ eep use ezroot <path>
$ eep contentobject contentnode <content object id>
or
$ eep use ezroot <path>
$ eep use contentobject <content object id>
$ eep contentobject contentnode
```

## republish
Republishes a content object.
```sh
$ eep use ezroot <path>
$ eep use contentobject <object id>
$ eep contentobject republish
or
$ eep contentobject republish <object id>
```

## sitemapxml
Displays a line of XML for inclusion in a sitemap.
- Note domain is only: ```example.com```
- Note ```<change frequency>``` is one of: ```always``` ```hourly``` ```daily``` ```weekly``` ```monthly``` ```yearly``` ```never```
- Note that the sitemap header is: ```<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">```
- the matching close is: ```</urlset>```

```sh
$ eep contentobject sitemapxml <object id> <domain> [<change frequency> [<priority>]]
```

## deleteversions
Deletes all the archived versions of a content object.
```sh
$ eep contentobject deleteversions <object id>
```

## fetchbyremoteid
Fetches a content object by remote id.
```sh
$ eep use ezroot <path>
$ eep contentobject fetchbyremoteid <remoteid>
```

# Modules - create
> The create module provides methods to create content objects.

```NOTE: Currently only generates random content based on a given content class and
given parent node.```

- [content](#content)
- [quick](#quick)

## content
Creates content object and fills it with random data.   
_Example_:
```sh
for i in {1..10}; do /usr/bin/eep create content random; echo \$i; done
```

```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep use contentnode <parent node id>
$ eep create content random
```

## quick
Creates an empty content object, returns object id and node id so that the object can be populated.   
_The output is, e.g._:
```sh
new object id 315
new node id 312
```
```sh
$ eep create quick <parent node id> <class identifier>
```

# Modules - crondaemon
> The crondaemon module ...

- [addtask](#addtask)

## addtask
Adds a cron task.
- Priority is ```1``` to ```999```, ```999``` is the highest

```sh
$ eep crondaemon addtask <task type> <task> <priority = 500>
```

# Modules - ezfind
> The ezfind module provides methods to manipulate and query eZFind's Solr index.

- [advanced](#advanced)
- [indexobject](#indexobject)
- [indexnode](#indexnode)
- [isobjectindexed](#isobjectindexed)
- [eject](#eject)
- [fields](#fields)
- [lastindexed](#lastindexed)
- [startsolr](#startsolr)
- [testquery](#testquery)

## advanced
Queries the Solr index.
- Using ```--output``` requires the relevant queryResponseWriter to be enabled in ```ezfind/java/solr/conf/solrconfig.xml```

```sh
$ eep ezfind advanced <statement> <fields to return> <filter> [--offset=## --limit=## --show-complex=1 --output=xml|csv|json]
```
_Example_:
```sh
$ eep ezfind advanced 'Water*' 'meta_node_id_si,attr_title_s' 'meta_class_identifier_ms:article' --show-complex=1 --output=json
```

## indexobject
Adds a content object's data to the Solr index; by content object id.
```sh
$ eep ezfind indexobject <object id>
```

## indexnode
Adds a content object's data to the Solr index; by content node id.
```sh
$ eep ezfind indexnode <node id>
```

## isobjectindexed
Checks if a content object is part of the Solr index.
```sh
$ eep ezfind isobjectindexed <object id>
```

## eject
Removes a content object's data from the Solr index.
```sh
$ eep ezfind eject <object id>
```

## fields
Displays the given content objects fields in the Solr index.
```sh
$ eep ezfind fields <object id>
```

## lastindexed
Displays when the content object was last indexed.
```sh
$ eep ezfind lastindexed <object id>
```

## startsolr
Checks if Solr is running; and starts it if it isn't.
```sh
$ eep use ezroot .
$ eep ezfind startsolr
```

## testquery
Runs a test query and var_dumps() results.
```sh
$ eep use ezroot .
$ eep ezfind testquery
```

# Modules - ezflow
> The ezflow module provides methods to find ezflow content classes and display information about ezflow blocks and block nodes.

- [find](#find)
- [list](#list)

## find
Returns content classes using the ezpage attribute.   
OR   
Returns content objects using a specific block type.
```sh
$ eep ezflow find ezpage
or 
$ eep ezflow find blocktype <block_type>
```

## list
Lists all ezflow block types in use.
```sh
$ eep ezflow list blocktypes [grouped]
```

# Modules - help
> The help module display module specific help.

- [help](#help)

## help
Displays module specific help.
```sh
$ eep help
or
$ eep <module> help
or
$ eep help <module>
```

# Modules - knowledgebase
> The knowledgebase module provides various helper methods.

- [ezdebug](#ezdebug)
- [vhost](#vhost)
- [sqltofixenglish](#sqltofixenglish)

## ezdebug
Displays useful INI settings to set up for debugging.
```sh
$ eep knowledgebase ezdebug
```

## vhost
Displays a useful apache virtual host file.
```sh
$ eep knowledgebase vhost
```

## sqltofixenglish
Displays some SQL that will convert all the UK translations of content classes to US translations.
```sh
$ eep knowledgebase sqltofixenglish
```

# Modules - list
> The list module provides methods to display information for eZPublish content and settings.

- [contentclasses](#contentclass)
- [attributes](#attributes)
- [allattributes](#allattributes)
- [children](#children)
- [siteaccesses](#siteaccess)
- [allinifiles](#allinifiles)
- [subtree](#subtree)
- [subtreeordered](#subtreeordered)
- [extensions](#extensions)

## contentclasses
Lists all content classes.
```sh
$ eep use ezroot <path>
$ eep list contentclasses
```

## attributes
Lists attributes of a content class.
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep list attributes
or
$ eep list attributes <class identifier>
```

## allattributes
Lists all attributes present in the system.
```sh
$ eep use ezroot <path>
$ eep list allattributes
```

## children
Lists children of a node.
- supports ```--limit=N``` and/or ```--offset=M```

```sh
$ eep use ezroot <path>
$ eep use contentnode <node id>
$ eep list children [--offset=<N>] [--limit=<M>]
or
$ eep list children <node id> [--offset=<N>] [--limit=<M>]
```

## siteaccesses
Lists all siteaccesses.
```sh
$ eep use ezroot <path>
$ eep list siteaccesses
```

## allinifiles
Lists all INI files.
```sh
$ eep use ezroot <path>
$ eep list inifiles
```

## subtree
Lists all nodes in a subtree.
- supports ```--limit=N``` ```--offset=M```

```sh
$ eep use ezroot <path>
$ eep use contentnode <node id>
$ eep list subtree
  or
$ eep list subtree <node id>
```

## subtreeordered
Lists all nodes in a subtree; with additional options
- like "list subtree" but works on more nodes; can order results in depthfirst(postorder) or breadthfirst order
- supports ```--order=<[depthfirst|breadthfirst]>``` ```--limit=N``` ```--offset=M``` and ```--truncate=P```

```sh
$ eep use ezroot <path>
$ eep use contentnode <node id>
$ eep list subtreeordered
or
$ eep list subtreeordered <node id>
```

## extensions
Lists all extensions.
```sh
$ eep use ezroot <path>
$ eep list extensions
```

# Modules - section
> The section module provides methods to display section information.

- [list](#list)
- [allobjects](#allobjects)

## list
List all sections.
```sh
$ eep section list
```

## allobjects
Lists all content objects in the section.
- supports ```--limit=N``` and/or ```--offset=M```

```sh
$ eep section allobjects <section id>
```

# Modules - trash
> The trash module provides methods to display information about content objects in the trash.

- [count](#count)
- [list](#list)

## count
Displays count of all content objects in the trash.
```sh
$ eep trash list
```

## list
Lists all content objects in the trash.
```sh
$ eep trash list
```

# Modules - use
> The use module provides methods to interact with the eep cache.

- [use](#use)
- [dump](#dump)

Saves values for use with other commands. The 'commands' are the keys.   
Note that "ezroot" is required when you are going to interact with eZ Publish.

## use
Adds a key/value pair to the cache.
```sh
$ eep use <key> <desired value>
```

## dump
Prints the current cached values.
```sh
$ eep use dump
```

# Core - AttributeFunctions
> Still to come ...

# Core - eepCache
> Still to come ...

# Core - eepHelpers
> Still to come ...

# Core - eepLog
> Still to come ...

# Core - eepValidate
> Still to come ...

# Extending - Bash completion
> Still to come ...

# Extending - New module
> Still to come ...

# Extending - New module method
> Still to come ...

