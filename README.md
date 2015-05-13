# eep
eep is a command line tool to support developers using ezpublish

## Usage

To set the ezpublish instance used with eep and list some content
classes for future modifications:

```sh
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
This is the list of modules (more or less up to date):

* attribute
* contentclass
* contentclassgroup
* contentnode
* contentobject
* create
* crondaemon
* ezfind
* ezflow
* help
* knowledgebase
* list
* section
* trash
* use

## Installation

Plug-and-play installation style:

1. copy the eep repository to your server
2. set a terminal path alias for the eep command
3. execute "eep use ezroot ." for each ezpublish instance you want to use
4. enjoy

## Documentation

* [By category](docs/index.md)
* [One-page](docs/one_page.md)

## Author & Licensing

* Author = "Mugo Web"
* Copyright = "Copyright © 2012  Mugo Web"
* License = "GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007"

## Contributing

Want to contribute? Great! To contribute to the mugoweb eep repo:

1. Fork it.
2. Create a branch (`git checkout -b eep_NewFeature`)
3. Commit your changes (`git commit -am "Can now list ezpublish installations"`)
4. Push to the branch (`git push origin eep_NewFeature`)
5. Submit a Pull Request to mugoweb for your branch
