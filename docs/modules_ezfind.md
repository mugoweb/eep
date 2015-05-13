# Modules - ezfind
> The ezfind module provides methods to manipulate and query eZFind's Solr index.

- [advanced](#advanced)
- [indexobject](#indexobject)
- [indexnode](#indexnode)
- [isobjectindexed](#isobjectindexed)
- [eject](#eject)
- [fields](#fields)
- [lastindexed](#lastindexed)
- [startsolr](#startsolr)
- [testquery](#testquery)

## advanced
Queries the Solr index.
- Using ```--output``` requires the relevant queryResponseWriter to be enabled in ```ezfind/java/solr/conf/solrconfig.xml```

```sh
$ eep ezfind advanced <statement> <fields to return> <filter> [--offset=## --limit=## --show-complex=1 --output=xml|csv|json]
```
_Example_:
```sh
$ eep ezfind advanced 'Water*' 'meta_node_id_si,attr_title_s' 'meta_class_identifier_ms:article' --show-complex=1 --output=json
```

## indexobject
Adds a content object's data to the Solr index; by content object id.
```sh
$ eep ezfind indexobject <object id>
```

## indexnode
Adds a content object's data to the Solr index; by content node id.
```sh
$ eep ezfind indexnode <node id>
```

## isobjectindexed
Checks if a content object is part of the Solr index.
```sh
$ eep ezfind isobjectindexed <object id>
```

## eject
Removes a content object's data from the Solr index.
```sh
$ eep ezfind eject <object id>
```

## fields
Displays the given content objects fields in the Solr index.
```sh
$ eep ezfind fields <object id>
```

## lastindexed
Displays when the content object was last indexed.
```sh
$ eep ezfind lastindexed <object id>
```

## startsolr
Checks if Solr is running; and starts it if it isn't.
```sh
$ eep use ezroot .
$ eep ezfind startsolr
```

## testquery
Runs a test query and var_dumps() results.
```sh
$ eep use ezroot .
$ eep ezfind testquery
```

