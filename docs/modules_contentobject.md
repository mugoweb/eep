# Modules - contentobject
> The contentobject module provides methods to manipulate content objects.

- [clearcache](#clearcache)
- [info](#info)
- [datamap](#datamap)
- [delete](#delete)
- [dump](#dump) _x-ref:_ `eep contentnode dump`
- [related](#related)
- [reverserelated](#reverserelated)
- [contentnode](#contentnode)
- [republish](#republish)
- [sitemapxml](#sitemapxml)
- [deleteversions](#deleteversions)
- [fetchbyremoteid](#fetchbyremoteid)
- [translationcreate](#translationcreate)
- [translationsetmain](#translationsetmain)
- [translationremove](#translationremove)
- [stateassignbyid](#stateassignbyid)
- [stateassignbyidentifier](#stateassignbyidentifier)
- [stateview](#stateview)

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

## dump
Dump all content data, suitable for export from ez. See "[eep contentnode dump](modules_contentnode.md#dump)".

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

## translationcreate
Adds a new translation for the content object, optionally copies the translation from an existing one
- Note that 'locale's are, eg., eng-GB or eng-US
```sh
$ eep contentobject translationcreate <object id> <new locale> [<existing locale>]
```

## translationsetmain
Sets the main translation, eg. in preparation to removing eng-GB as a supported translation
```sh
$ eep contentobject translationsetmain <object id> <locale>
```

## translationremove
Removes a translation from the content object
```sh
$ eep contentobject translationremove <object id> <locale>
```

## stateassignbyid
Assigns an object state by state id
```sh
eep contentobject stateassignbyid <object id> <state id>
```

## stateassignbyidentifier
Assigns an object state by state/group identifier e.g. ez_lock/locked
```sh
eep contentobject stateassignbyidentifier <object id> <state/group identifier>
```

## stateview
Displays object state information
```sh
eep contentobject stateview <object id>
```

