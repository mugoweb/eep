#Modules - contentclass
> The contentclass module provides methods to manipulate content classes.

- [appendtogroup](#appendtogroup)
- [createclass](#createclass)
- [deleteclass](#deleteclass)
- [fetchallinstances](#fetchallinstances)
- [info](#info)
- [listattributes](#listattributes)
- [setclassobjectidentifier](#setclassobjectidentifier)
- [setfield](#setfield)
- [setiscontainer](#setiscontainer)
- [removefromgroup](#removefromgroup)

## appendtogroup
Append the content class to the content class group. Note, a class can exist in more than one group.
```sh
$ eep contentclass appendtogroup <content class identifier> <group identifier>
```

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

## fetchallinstances
Fetches all instances of a contentclass.
_Note that this supports limit and offset parameters._
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep contentclass fetchallinstances
or
$ eep contentclass fetchallinstances <content class identifier> [--limit=nnn] [--offset=nnn]
```

## info
Dumps the internal fields and values that ez uses to specify a content class. These can be edited with eep setfield.
```sh
$ dumps the internal fields that ez manages for the content class, like 'url pattern' and etc.
```

## listattributes
Lists all class attributes.
```sh
$ eep use ezroot <path>
$ eep use contentclass <class identifier>
$ eep contentclass listattributes
```

## removefromgroup
Removes a contentclass from a contentclass group.
```sh
$ eep use ezroot <path>
$ eep contentclass removefromgroup <content class identifier> <group identifier>
```

## setclassobjectidentifier
Sets the string used to name instances of the class, uses the same syntax as in the admin UI.
```sh
$ eep contentclass setclassobjectidentifier <class identifier> <object naming string or pattern>
```

## setfield
Set any of the internal fields that ez manages for the content class, see eep info for the list of fields and values.
```sh
$ eep contentclass setfield <content class identifier> <field name> <new value>
```

## setiscontainer
Sets or unsets the 'is container' flag on the class.
```sh
$ eep contentclass setiscontainer <class identifier> <0|1>
```
