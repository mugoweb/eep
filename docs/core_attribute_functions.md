# Core - AttributeFunctions
> A collection of static attribute related helper functions.

- [updateAttribute](#updateattribute)
- [addAttributeToClass](#addattributetoclass)
- [addAttributeToClass](#addattributetoclass)
- [updateContentObjectAttributes](#updatecontentobjectattributes)
- [deleteAttribute](#deleteattribute)
- [listAttributes](#listattributes)
- [fromString](#fromstring)
- [toString](#tostring)
- [createAlias](#createalias)
- [setField](#setfield)
- [info](#info)
- [contentobjectid](#contentobjectid)


## updateAttribute
> Updates an attribute for an existing content class. If the attribute doesn't exist it will be created via `AttributeFunctions::addAttributeToClass`. All content class objects will be updated.  
If the attribute does exist all content class objects will be updated only e.g. repairing in case an update via the admin UI timed out pre-maturely.

*Parameters:* 
- `$classIdentifier` String
- `$newAttributeXPath` new attribute XML (see example below)

`$newAttributeXPath` is expected to use the pre-defined `$newAttributeXML` format. (Available as public static and set in the constructor)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<newattribute>
    <identifier>
        the_identifier
    </identifier>
    <displayname>
        Display Name
    </displayname>
    <description>
        This is the description of this attribute. You can say anything you like.
    </description>

    <!-- supported: ezstring ezobjectrelationlist ezinteger ezselection ezxmltext ezimage and probably others -->
    <!-- see content.ini for full list of avilable types -->
    <datatypestring>ezxmltext</datatypestring>
    
    <!-- some examples: eng-GB eng-CA eng-US -->
    <language>eng-CA</language>
    
    <is_required>0</is_required>
    <is_searchable>1</is_searchable>
    <is_information_collector>0</is_information_collector>
    <can_translate>0</can_translate>
    
    <!-- "eep-no-content" is recognized to mean "no content" -->
    <content>eep-no-content</content>

    <additional_for_specific_datatype>
        <ezselection>
            <is_multi_select>
                0
            </is_multi_select>
            <options>
                <option>Class</option>
                <option>Order</option>
                <option>Family</option>
                <option>Subfamily</option>
                <option>Genus</option>
                <option>Species</option>
                <option>IncertaeSedis</option>
            </options>
        </ezselection>

        <ezstring>
            <!-- maxstringlength is capped at 255 by a sanity check in the code -->
            <maxstringlength>255</maxstringlength>
        </ezstring>

        <ezxmltext>
            <!-- numberoflines is capped at 30 by a sanity check in the code -->
            <numberoflines>10</numberoflines>
        </ezxmltext>
        
        <ezboolean>
            <default_value>eep-no-content</default_value>
        </ezboolean>

        <ezobjectrelation>
            <selection_type>
                0
            </selection_type>
            <fuzzy_match>
                false
            </fuzzy_match>
            <!-- node id, url path, or "eep-no-content" -->
            <default_selection_node>
                eep-no-content
            </default_selection_node>
        </ezobjectrelation>

        <!-- not fully supported
        <ezmatrix>
            <default_row_count>
                3
            </default_row_count>
        </ezmatrix>
        -->
    </additional_for_specific_datatype>
</newattribute>
```

## addAttributeToClass
> Adds a new attribute to an existing content class.

*Parameters:*
- `$contentClass` eZContentClass object
- `$newAttributeXPath` new attribute XML (see example above)

*Note:* `$contentClass` is the result of `eZContentClass::fetchByIdentifier( $classIdentifier );`  

*Returns:*
- The new contentClassAttributeId; Integer


## updateParameters 
> Updates optional attribute parameters like selection_type for objectrelations.

*Parameters:*
- `$classAttribute` eZContentClassAttribute object
- `$newAttributeXPath` new attribute XML (see example above)


## updateContentObjectAttributes
> Update all the objects with the new attribute info.

*Parameters:*
- `$contentClass` eZContentClass object
- `$classAttributeID` Integer
- `$identifier` Boolean; default = false


## deleteAttribute
> Deletes an attribute from a content class.

*Parameters:*
- `$classIdentifier` String
- `$attributeIdentifier` String


## listAttributes
> Lists all content class attributes (table output)

*Parameters:*
- `$classIdentifier` String


## fromString
> Updates a content object's attribute value.

*Parameters:*
- `$contentObjectId` Integer
- `$attributeIdentifier` String
- `$value` Mixed

`Note:` each data type has different input string format requirements, consult the link below for details.
- [fromString documentation](http://svn.projects.ez.no/data_import/doc/fromString.txt) ( [Mirror](http://www.ezpedia.org/ez/simple_fromstring_and_tostring_interface_for_attributes) )


## toString
> Returns string representation of a content object attribute value.

*Parameters:*
- `$contentObjectId` Integer
- `$attributeIdentifier` String


## createAlias
> Creates and image alias for a given content object attribute.

*Parameters:*
- `$contentObjectId` Integer
- `$attributeIdentifier` String
- `$aliasName` String


## contentobjectid
> Returns the contentobject id from a contentobject _attribute_ id.

*Parameters:*
- `$contentObjectAttributeId` Integer
- `$version` Integer; Default = 1

*Returns:*
- Integer

