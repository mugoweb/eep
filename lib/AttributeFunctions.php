<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * AttributeFunctions.php
 */

$AttributeFunctions_newAttributeXML = <<<AttributeFunctions_XML
<?xml version="1.0" encoding="UTF-8"?>
<!--
Validation:
identifier  == lowercase letters and underscore
display     == letters, numbers, simple special chars
bit         == 0 or 1
-->
<newattribute>
    <identifier required="required" validation="identifier">
        parrot_says
    </identifier>
    <displayname required="required" validation="display">
        Who's a pretty polly?
    </displayname>
    <!-- ezstring ezobjectrelationlist -->
    <!-- see content.ini for list of avilable types -->
    <datatypestring required="required" validation="identifier">
        ezstring
    </datatypestring>
    <!-- eng-GB eng-CA eng-US -->
    <language required="required" validation="language">
        eng-CA
    </language>
    <is_required validation="bit">
        0
    </is_required>
    <is_searchable validation="bit">
        0
    </is_searchable>
    <is_information_collector validation="bit">
        0
    </is_information_collector>
    <can_translate validation="bit">
        0
    </can_translate>

    <!-- unclear just what this is -->
    <content required="nullable">
        null
    </content>

    <additional_for_specific_datatype>
        <ezboolean>
            <default_value>
                0
            </default_value>
        </ezboolean>

        <ezobjectrelation>
            <selection_type>
                0
            </selection_type>
            <fuzzy_match>
                false
            </fuzzy_match>
            <default_selection_node>
                <!-- integer, 0 means "not set" -->
                0
            </default_selection_node>
        </ezobjectrelation>

        <ezmatrix>
            <default_row_count>
                3
            </default_row_count>
        </ezmatrix>
    </additional_for_specific_datatype>
</newattribute>
AttributeFunctions_XML;

class AttributeFunctions
{
    public static $someLanguages = array("cat-ES","chi-CN","chi-HK","chi-TW","cro-HR","cze-CZ","dan-DK","dut-NL","ell-GR","eng-AU","eng-CA","eng-GB","eng-NZ","eng-US","esl-ES","esl-MX","fin-FI","fre-BE","fre-CA","fre-FR","ger-DE","heb-IL","hin-IN","hun-HU","ind-ID","ita-IT","jpn-JP","kor-KR","nno-NO","nor-NO","pol-PL","por-BR","por-MZ","por-PT","rus-RU","ser-SR","slk-SK","srp-RS","swe-SE","tur-TR","ukr-UA");
    public static $newAttributeXML = null;

    //--------------------------------------------------------------------------
    function __construct()
    {
        global $AttributeFunctions_newAttributeXML;
        $this->newAttributeXML = $AttributeFunctions_newAttributeXML;
    }


/*

todo, add these to the xml and confirm that they are interpreted correctly upon
attribute creation

// ezmatrix specific values
$params['matrix'] = array();
$params['matrix']['type'] = 'Type';
$params['matrix']['path'] = 'Path';
$params['matrix']['title'] = 'Title';
$params['matrix']['site_name'] = 'Site Name';
$params['default_row_count'] = 0;


*/

    //--------------------------------------------------------------------------
    // this is the entry point for creating a new attribute
    static public function updateAttribute( $classIdentifier, $parameters )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $classDataMap = $contentClass->attribute('data_map' );
        if( !isset( $classDataMap[ $parameters[ 'identifier' ] ] ) )
        {
            // attribute is not set in the class, so add it to the class
            $classAttributeID = AttributeFunctions::addAttributeToClass( $contentClass, $parameters );
            // update all the objects with the new attribute
            AttributeFunctions::updateContentObjectAttributes( $contentClass, $classAttributeID, $parameters[ 'identifier' ] );
        }
        else
        {
            // in case we're repairing an attribute that was added in the ui and
            // timed out before all the objects were updated
            $classAttributeID = $classDataMap[ $parameters[ 'identifier' ] ]->attribute( 'id' );
            AttributeFunctions::updateContentObjectAttributes( $contentClass, $classAttributeID, $parameters[ 'identifier' ] );
        }
    }

    //--------------------------------------------------------------------------
    // note that $contentClass is the return from:
    // $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
    //
    static function addAttributeToClass( $contentClass, $data )
    {
        $classID = $contentClass->attribute( "id" );

        // create new attribute
        $attributeCreationInfo = array
        (
            "identifier"                    => $data[ "identifier" ]
            , "serialized_name_list"        => serialize( array( $data[ "language" ] => $data[ "displayname" ], "always-available" => $data[ "language" ] ) )
            , "can_translate"               => $data[ "can_translate" ]
            , "is_required"                 => $data[ "is_required" ]
            , "is_searchable"               => $data[ "is_searchable" ]
            , "is_information_collector"    => $data[ "is_information_collector" ]
        );

        $newAttribute = eZContentClassAttribute::create( $classID, $data[ "datatypestring" ], $attributeCreationInfo  );
        $dataType = $newAttribute->dataType();
        if( !$dataType )
        {
            throw new Exception( "Unknown datatype: [ " .$datatype. " ]" );
        }
        $dataType->initializeClassAttribute( $newAttribute );
        $newAttribute->store();
        AttributeFunctions::updateParameters( $newAttribute, $data );
        $newAttribute->sync();

        if( $data[ "content" ] )
        {
            $newAttribute->setContent( $data[ "content" ] );
        }

        // store attribute, update placement, etc...
        $allAttributesList = $contentClass->fetchAttributes();
        $allAttributesList[] = $newAttribute;

        // remove temporary version
        if ( $newAttribute->attribute( "id" ) !== null )
        {
            $newAttribute->remove();
        }

        $newAttribute->setAttribute( "version", eZContentClass::VERSION_STATUS_DEFINED );
        $newAttribute->setAttribute( "placement", count( $allAttributesList ) );

        $contentClass->adjustAttributePlacements( $allAttributesList );
        foreach( $allAttributesList as $attribute )
        {
            $attribute->storeDefined();
        }
        $classAttributeID = $newAttribute->attribute( "id" );

        echo  "\n\nAttribute with ID " .$classAttributeID . " added\n\n";

        return $classAttributeID;
    }

    //--------------------------------------------------------------------------
    /*
     * Update optional attribute parameters like selection_type for objectrelations
     */
    static function updateParameters( $classAttribute, $params )
    {
        $content = $classAttribute->content();

        switch( $classAttribute->DataTypeString )
        {
            case "ezboolean":
                if( isset( $params[ "default_value" ] ) && $params[ "default_value" ] !== false  )
                {
                    $classAttribute->setAttribute( "data_int3", $params[ "default_value" ] );
                }
                break;

            case "ezobjectrelation":
                {
                    $content[ "selection_type" ] = 0;
                    if ( isset( $params[ "selection_type" ] ) )
                    {
                        $content[ "selection_type" ] = $params[ "selection_type" ];
                    }
                    $content[ "fuzzy_match" ] = false;
                    if ( isset( $params[ "fuzzy_match" ] ) )
                    {
                        $content[ "fuzzy_match" ] = $params[ "fuzzy_match" ];
                    }
                    $content[ "default_selection_node" ] = false;
                    if ( isset( $params[ "default_selection_node" ] ) )
                    {
                        if( is_numeric( $params[ "default_selection_node" ] ) )
                        {
                            $content[ "default_selection_node" ] = $params[ "default_selection_node" ];
                        }
                        else
                        {
                            $node = eZContentObjectTreeNode::fetchByURLPath( $params[ "default_selection_node" ] );
                            if( $node )
                            {
                                $content[ "default_selection_node" ] = $node->attribute( "node_id" );
                            }
                        }
                    }
                    $classAttribute->setContent( $content );
                    $classAttribute->store();
                }
                break;

            case "ezmatrix":
                {
                    $matrix = new eZMatrixDefinition();
                    if( !empty( $params[ "matrix" ] ) )
                    {
                        foreach( $params[ "matrix" ] as $identifier => $name )
                        {
                            $matrix->addColumn( $name, $identifier );
                        }
                    }
                    $classAttribute->setContent( $matrix );
                    $classAttribute->setAttribute( "data_int1", $params[ "default_row_count" ] );
                    $classAttribute->store();
                }
                break;

            default:
        }
    }

    /**
     * update all the objects with the new attribute info
     * todo; this might have to support processing in batches
     */
    static function updateContentObjectAttributes( $contentClass, $classAttributeID, $identifier = false )
    {
        $classId = $contentClass->attribute( "id" );
        // update object attributes
        $objects = eZContentObject::fetchSameClassList( $classId, false );
        $numObjects = count( $objects );

        foreach( $objects as $num => $object )
        {
            $object = eZContentObject::fetch( $object[ "id" ] );
            if( $object )
            {
                $contentobjectID = $object->attribute( "id" );
                $objectVersions = $object->versions();
                foreach( $objectVersions as $objectVersion )
                {
                    $translations = $objectVersion->translations( false );
                    $version = $objectVersion->attribute( "version" );
                    $dataMap = $objectVersion->attribute( "data_map" );
                    if( $identifier && isset( $dataMap[ $identifier ] ) )
                    {
                       // Attribute already exists for this object version
                    }
                    else
                    {
                        foreach( $translations as $translation )
                        {
                            $objectAttribute = eZContentObjectAttribute::create( $classAttributeID, $contentobjectID, $version );
                            $objectAttribute->setAttribute( "language_code", $translation );
                            $objectAttribute->initialize();
                            $objectAttribute->store();
                            $objectAttribute->postInitialize();
                        }
                    }
                }
                unset( $GLOBALS[ "eZContentObjectContentObjectCache" ] );
                unset( $GLOBALS[ "eZContentObjectDataMapCache" ] );
                unset( $GLOBALS[ "eZContentObjectVersionCache" ] );
                unset( $object );
            }
            echo "Percent complete: " . sprintf( "% 3.3f", ( ($num+1.0) / $numObjects)*100.0 ) . "%\r";
            unset( $objects[ $num ] );
        }
        echo "\n";
    }

    //--------------------------------------------------------------------------
    static function deleteAttribute( $classIdentifier, $attributeIdentifier )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $classDataMap = $contentClass->attribute( "data_map" );
        if( !isset( $classDataMap[ $attributeIdentifier ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $attributeIdentifier . "]" );

        // remove the attribute from all the objects that have it
        $classAttribute = $classDataMap[ $attributeIdentifier ];
        $objectAttributeInstances = eZContentObjectAttribute::fetchSameClassAttributeIDList( $classAttribute->attribute( "id" ) );
        $numObjects = count( $objectAttributeInstances );
        if( $numObjects )
        {
            echo "Updating " . $numObjects . " objects.\n";
            foreach( $objectAttributeInstances as $num => $objectAttribute )
            {
                // all the magic in the next 2 lines
                $objectAttributeID = $objectAttribute->attribute( "id" );
                $objectAttribute->removeThis( $objectAttributeID );
                echo "Percent complete: " . sprintf( "% 3.3f", (($num+1) / $numObjects)*100.0 ) . "%\r";
                unset( $GLOBALS[ "eZContentObjectContentObjectCache" ] );
                unset( $GLOBALS[ "eZContentObjectDataMapCache" ] );
                unset( $GLOBALS[ "eZContentObjectVersionCache" ] );
            }
            echo "\nDone updating objects.\n";
        }
        // remove the attribute from the class
        $attributes = $contentClass->fetchAttributes();
        foreach( $attributes as $index => $attribute )
        {
            if( $attribute->attribute( "identifier" ) == $attributeIdentifier )
            {
                $attributes[ $index ]->removeThis();
                break;
            }
        }
        echo "Done removing attribute.\n";
    }

    //--------------------------------------------------------------------------
    static function listAttributes( $classIdentifier )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "This content class does not exist: [" . $classIdentifier . "]" );

        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $attributeList = eZContentClassAttribute::fetchListByClassID( $classId );

        $results = array();
        $results[] = array
        (
            "Identifier"
            , "Lang"
            , "Id"
            , "Type"
            , "Srch"
            , "Reqd"
            , "Info"
            , "Pos"
            , "Name"
        );
        foreach( $attributeList as $attribute )
        {
            $snl = unserialize( $attribute->SerializedNameList );
            $results[] = array
            (
                $attribute->Identifier
                , $snl[ "always-available" ]
                , $attribute->ID
                , $attribute->DataTypeString
                , $attribute->IsSearchable
                , $attribute->IsRequired
                , $attribute->IsInformationCollector
                , $attribute->Position
                , $snl[ $snl[ "always-available" ] ]
            );
        }
        eep::printTable( $results, "list attributes of class: ".$classIdentifier );
    }

    //--------------------------------------------------------------------------
    public static function fromString( $contentObjectId, $attributeIdentifier, $value )
    {
        $contentObject = eZContentObject::fetch( $contentObjectId );
        if( !is_object( $contentObject ) )
        {
            throw new Exception( "This is not an object id [" .$contentObjectId. "]" );
        }
        $dataMap = $contentObject->dataMap();
        if( !isset( $dataMap[ $attributeIdentifier ] ) )
        {
            throw new Exception( "This is not an attribute identifer [" .$attributeIdentifier. "] on content class '" . $contentObject->ClassIdentifier . "'"  );
        }

        $dataMap[ $attributeIdentifier ]->FromString( $value );
        $dataMap[ $attributeIdentifier ]->store();

        eep::republishObject( $contentObjectId );
    }

    //--------------------------------------------------------------------------
    public static function toString( $contentObjectId, $attributeIdentifier )
    {
        $contentObject = eZContentObject::fetch( $contentObjectId );
        if( !is_object( $contentObject ) )
        {
            throw new Exception( "This is not an object id [" .$contentObjectId. "]" );
        }
        $dataMap = $contentObject->DataMap();
        if( !isset( $dataMap[ $attributeIdentifier ] ) )
        {
            throw new Exception( "This is not an attribute identifer [" .$attributeIdentifier. "] on content class '" . $contentObject->ClassName . "'"  );
        }
        return $dataMap[ $attributeIdentifier ]->ToString();
    }

    //--------------------------------------------------------------------------
    public static function createAlias( $contentObjectId, $attributeIdentifier, $aliasName )
    {
        $contentObject = eZContentObject::fetch( $contentObjectId );
        if( !is_object( $contentObject ) )
        {
            throw new Exception( "This is not an object id [" .$contentObjectId. "]" );
        }
        $dataMap = $contentObject->DataMap();
        if( !isset( $dataMap[ $attributeIdentifier ] ) )
        {
            throw new Exception( "This is not an attribute identifer [" .$attributeIdentifier. "] on content class '" . $contentObject->ClassName . "'"  );
        }
        $imgAttribute       = $dataMap[ $attributeIdentifier ];
        $imageAliasHandler = new eZImageAliasHandler( $imgAttribute );
        $result = $imageAliasHandler->imageAlias( $aliasName );
        return "Command executed successfully \n";
    }

    //--------------------------------------------------------------------------
    public static function setField( $classIdentifier, $attributeIdentifier, $fieldIdentifier, $fieldValue )
    {
        if( !isset( $fieldValue ) || $fieldValue == null)
        {
            $fieldValue = "";
        }
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );

        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $classDataMap = $contentClass->attribute( "data_map" );

        if( !isset( $classDataMap[ $attributeIdentifier ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $attributeIdentifier . "]" );

        $previousvalue = $classDataMap[ $attributeIdentifier ]->attribute( $fieldIdentifier );
        if( is_array( $previousvalue ) )
        {
            switch( $fieldIdentifier )
            {
                case "display_info":
                    echo "Changing $fieldIdentifier is not currently supported";
//                    $classAttribute->DisplayInfo = unserialize( $fieldValue );
                    break;
                case "nameList":
                    echo "Changing $fieldIdentifier is not currently supported";
                    break;
                case "descriptionList":
                    echo "Changing $fieldIdentifier is not currently supported";
                    break;
                default:
                    echo "Changing $fieldIdentifier is not currently supported";
                    return;
            }
//            $classAttribute->store( $fieldIdentifier );
        }
        else
        {
            $classAttribute = $classDataMap[ $attributeIdentifier ];
            $classAttribute->setAttribute( $fieldIdentifier, $fieldValue );
            $classAttribute->store( $fieldIdentifier );
        }
    }

    //--------------------------------------------------------------------------
    public static function info( $classIdentifier, $attributeIdentifier, $fieldIdentifier )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $classDataMap = $contentClass->attribute( "data_map" );
        if( !isset( $classDataMap[ $attributeIdentifier ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $attributeIdentifier . "]" );

        $fieldList = $classDataMap[ $attributeIdentifier ]->attributes();

        $results[] = array("Field Name", "Field Value", "Value Type" );
        foreach($fieldList as $fieldName)
        {
            $value = $classDataMap[ $attributeIdentifier ]->attribute( $fieldName );
            $type  = "string";
            if( is_array( $value ) )
            {
                $value = serialize($value);
                $type  = "Array";
            }

            $results[]=array(
                        $fieldName
                    ,   $value
                    ,   $type
                    );
        }

        eep::printTable( $results, "Class attribute fields");
    }
}
?>