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

