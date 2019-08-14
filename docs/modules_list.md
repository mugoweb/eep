# Modules - list
> The list module provides methods to display information for eZPublish content and settings.

- [allattributes](#allattributes)
- [allinifiles](#allinifiles)
- [attributes](#attributes)
- [children](#children)
- [contentclasses](#contentclass)
- [extensions](#extensions)
- [links](#links)
- [siteaccesses](#siteaccess)
- [subtree](#subtree)
- [subtreeordered](#subtreeordered)

## allattributes
Lists all attributes present in the system.
```sh
$ eep use ezroot <path>
$ eep list allattributes
```

## allinifiles
Lists all INI files.
```sh
$ eep use ezroot <path>
$ eep list inifiles
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

## extensions
Lists all extensions.
```sh
$ eep use ezroot <path>
$ eep list extensions
```

## links
List all ez links, so to review all the outbound links on the site.
The output is CSV and can take quite a while to generate since it pings all destinations.
```sh
  eep use ezroot <path>
  eep list links <public domain and protocol> <admin domain and protocol> <node view path>
  where:
    <public domain and protocol> is for the public side, eg http://foo.com
    <admin domain and protocol> eg, https://admin.foo.com
    <node view path> eg, /manage/content/view/full/
```

## contentclasses
Lists all content classes.
```sh
$ eep use ezroot <path>
$ eep list contentclasses
```

## siteaccesses
Lists all siteaccesses.
```sh
$ eep use ezroot <path>
$ eep list siteaccesses
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
