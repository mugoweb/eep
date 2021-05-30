# Modules - section
> The section module provides methods to display section information.

- [list](#list)
- [allobjects](#allobjects)
- [assign](#assign)

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

## assign
- assign section to subtree

```sh
$ eep section <section id> <node id>
```

