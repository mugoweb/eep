# Core - eepHelpers
> A collection of core helper methods.

- [printTable](#printtable)
- [getListOfAliases](#getlistofaliases)
- [expandAliases](#expandaliases)
- [fastRelatedObjectCount](#fastrelatedobjectcount)
- [displayNodeList](#displaynodelist)
- [displayNonObjectList](#displaynonobjectlist)
- [displayObjectList](#displayobjectlist)
- [extractAdditionalParams](#extractadditionalparams)
- [republishObject](#republishobject)
- [convertTimeToInteger](#converttimetointeger)
- [fixXML](#fixxml)
- [fixBadQuestionMarks](#fixbadquestionmarks)


## printTable
> Outputs data in a formatted ASCII table

*Parameters:*
- `$table` table data, with the first row listing the column headers; Array
- `$description` an optional table description; String

```php
$table = array
(
    [0] => array
    (
        'OID'
        , 'NID'
        , 'Title'
    ),
    [1] => array
    (
        12345
        , 12346
        , 'Lorem Ipsum'
    )
    // etc ...
);

eep::printTable( $table, "A dummy description" );

+--------+---------+--------------+
I A dummy description             |
+--------+---------+--------------+
I    OID |     NID |        Title |
+--------+---------+--------------+
|  12345 |   12346 |  Lorem Ipsum |
...

```

## getListOfAliases
> Returns an array of all module aliases

## expandAliases
> Returns the expanded version of the module name, e.g. cc => contentclass

*Parameters:*
- `$alias` the alias to expand; String

*Returns:*
- The expanded alias or the original `$alias` value if the alias is not found; String


## fastRelatedObjectCount
> Returns the (reverse)related object count for a given content object id.

*Parameters:*
- `$objectId` Integer
- `$objectVersion` Integer
- `$attributeID` Integer; Optional; Default = 0
- `$reverseRelatedObjects` Boolean; Optional; Default = false
- `$params` Boolean; Optional; Default = false

*Returns:*
- Integer


## displayNodeList
> Outputs an formatted ASCII table of node information, from a list of nodes.

*Parameters:*
- `$list` Array of eZContentObjectTreeNode(s)
- `$title` String


## displayNonObjectList
> Outputs an formatted ASCII table of node information, from a list of 'non-objects'.

`Note:` A non-object is the array of data that you get when you fetch an object but say that you don't actually want the object.

*Parameters:*
- `$list` Array of eZContentObjectTreeNode(s)
- `$title` String


## displayObjectList
> Outputs an formatted ASCII table of node information, from a list of content objects.

- `$list` Array of eZContentObject(s)
- `$title` String


## extractAdditionalParams
> Returns key value pairs based on any params to the command line that match: --key=value

*Parameters:*
- `&$args` Array


## republishObject
> Re-publishes a content object.

`Note:` This protects against accidentally operating on an object with no main node, i.e. an object that is in the trash

*Parameters:*
- `$objectId` Integer


## convertTimeToInteger
> Converts time string to number of seconds.

*Parameters:*
- `$time` String; hh:mm:ss

*Returns:*
- Integer


## fixXML
> Cleans many common forms of XML corruption, and ultimately forces character encoding.

`Note:` This method can render some remaining characters as question marks, but many of those can be fixed too, see [fixBadQuestionMarks](#fixbadquestionmarks).

*Parameters:*
- `$xml` XML String

*Returns:*
- XML String


## fixBadQuestionMarks
> Fixes 'bad' question marks that have been been introduced by forcing the encoding to utf8 (e.g. via [fixXML](#fixxml)).

*Parameters:*
- `$xml` XML String

*Returns:*
- XML String

