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

