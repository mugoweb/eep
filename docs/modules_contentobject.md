# Modules - contentobject
> The contentobject module provides methods to manipulate content objects.

- [clearcache](#clearcache)
- [info](#info)
- [datamap](#datamap)
- [delete](#delete)
- [related](#related)
- [reverserelated](#reverserelated)
- [contentnode](#contentnode)
- [republish](#republish)
- [sitemapxml](#sitemapxml)
- [deleteversions](#deleteversions)
- [fetchbyremoteid](#fetchbyremoteid)

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

