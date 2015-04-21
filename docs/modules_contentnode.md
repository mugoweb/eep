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

