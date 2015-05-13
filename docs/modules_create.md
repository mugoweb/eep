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

