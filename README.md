EEP
=============

EEP is a command line tool to support developers using ezpublish

Usage
-----

To set the ezpublish instance used with eep and list some content
classes for future modifications:

    eep use ezroot .
    eep list contentclasses

For additional help:

    eep help
    eep <module> help
    eep help <module>

### Modules
This is the list of modules (more or less up to date):
* attribute
* contentclass
* contentclassgroup
* contentnode
* contentobject
* create
* crondaemon
* ezfind
* help
* knowledgebase
* list
* section
* test
* trash
* use

Installation
------------
Plug-and-play installation style:

1. copy the eep repository to your server
2. set a terminal path alias for the eep command
3. enjoy

Author & Licensing
------------------
author = "Mugo Web"
copyright = "Copyright © 2012  Mugo Web"
license = "GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007"

Contributing
------------
Want to contribute? Great! To contribute to the mugoweb eep repo:

1. Fork it.
2. Create a branch (`git checkout -b eep_NewFeature`)
3. Commit your changes (`git commit -am "Can now list ezpublish installations"`)
4. Push to the branch (`git push origin eep_NewFeature`)
5. Submit a Pull Request to mugoweb for your branch
