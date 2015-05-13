<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/attribute/index.php
 */

class attribute_commands
{
    const attribute_delete          = "delete";
    const attribute_newattributexml = "newattributexml";
    const attribute_migrate         = "migrate";
    const attribute_update          = "update";
    const attribute_fromstring      = "fromstring";
    const attribute_tostring        = "tostring";
    const attribute_setfield        = "setfield";
    const attribute_info            = "info";
    const attribute_createalias     = "createalias";
    const attribute_contentobjectid = "contentobjectid";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::attribute_delete
        , self::attribute_fromstring
        , self::attribute_tostring
        , self::attribute_migrate
        , self::attribute_newattributexml
        , self::attribute_update
        , self::attribute_setfield
        , self::attribute_info
        , self::attribute_createalias
        , self::attribute_contentobjectid
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
delete
- deletes an attribute from class and objects
  eep attribute delete <class identifier> <attribute identifier>

fromstring
- calls FromString() on the attribute
  eep attribute fromstring <content object id> <attribute identifier> <new value>

tostring
- calls ToString on the attribute
  eep attribute tostring <content object id> <attribute identifier>

migrate
- copies data from one attribute to another within a content class
- todo, report available conversions
- currently supported are "rot13" for testing and "time2integer" and "trim" and "date2ts"
  eep attribute migrate <class identifier> <src attribute> <conversion> <dest attribute>

newattributexml
- dumps xml that can be edited and then imported
  eep attribute newattributexml

update
- updates objects with new attribute and also the class; will resume after a partial update.
  eep attribute update <class identifier> <path to newattributexml file>

setfield
- directly sets one of the attribute fields (e.g. data_int, data_text1 etc.)
  eep attribute setfield <class identifier> <attributename> <fieldname> <fieldvalue>

info
- displays all attribute fields (e.g. data_int, data_text1 etc.)
  eep attribute info <class identifier> <attributename> <fieldname>

createalias
- for an image attribute it creates a given alias manually
  eep attribute createalias <content object id> <attribute identifier> <alias name>

contentobjectid
- return the contentobject id from a contentobject attribute id
  eep attribute contentobjectid <content object _attribute_ id> [<version>]

EOT;
    }

    //--------------------------------------------------------------------------
    private function attribute_migrate( $classIdentifier, $srcAttribute, $conversion, $destAttribute )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $classDataMap = $contentClass->attribute( "data_map" );

        if( !isset( $classDataMap[ $srcAttribute ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $srcAttribute . "]" );

        if( !isset( $classDataMap[ $destAttribute ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $destAttribute . "]" );

        $classId = $contentClass->attribute( "id" );

        $objects = eZContentObject::fetchSameClassList( $classId, false );
        $numObjects = count( $objects );

        $conversionFunc = null;
        switch( $conversion )
        {
            default:
                echo "This mapping is not supported: [" .$conversion. "]\n";
                return;
                break;

            case "rot13";
                $conversionFunc = "convertStringToRot13";
                break;

            case "time2integer":
                $conversionFunc = "convertTimeToInteger";
                break;

            case "trim":
                $conversionFunc = "convertTrim";
                break;
            
            case "date2ts":
                $conversionFunc = "dateToTS";
                break;
        }

        foreach( $objects as $n => $object )
        {
            $object = eZContentObject::fetch( $object[ "id" ] );
            if( $object )
            {
                // copy data with conversion
                $dataMap = $object->DataMap();
                $src = $dataMap[ $srcAttribute ];
                $dest = $dataMap[ $destAttribute ];
                $dest->fromString( eep::$conversionFunc( $src->toString() ) );
                $dest->store();

                // publish to get changes recognized, eg object title updated
                eep::republishObject( $object->attribute( "id" ) );
            }
            echo "Percent complete: " . sprintf( "% 3.3f", ( ($n+1.0) / $numObjects)*100.0 ) . "%\r";
            // clear caches
            unset( $GLOBALS[ "eZContentObjectContentObjectCache" ] );
            unset( $GLOBALS[ "eZContentObjectDataMapCache" ] );
            unset( $GLOBALS[ "eZContentObjectVersionCache" ] );
            unset( $object );
        }
        echo "\n";
    }

    //--------------------------------------------------------------------------
    public function run( $argv, $additional )
    {
        $command = @$argv[2];
        $param1 = @$argv[3];
        $param2 = @$argv[4];
        $param3 = @$argv[5];
        $param4 = @$argv[6];

        if( !in_array( $command, $this->availableCommands ) )
        {
            throw new Exception( "Command '" . $command . "' not recognized." );
        }

        $eepCache = eepCache::getInstance();

        switch( $command )
        {
            case "help":
                echo "\nAvailable commands:: " . implode( ", ", $this->availableCommands ) . "\n";
                echo "\n".$this->help."\n";
                break;

            case self::attribute_delete:
                $classIdentifier = $param1;
                $attributeIdentifier = $param2;
                AttributeFunctions::deleteAttribute( $classIdentifier, $attributeIdentifier );
                break;

            case self::attribute_newattributexml:
                $attr = new AttributeFunctions();
                echo $attr->newAttributeXML;
                break;

            case self::attribute_update:
                $classIdentifier = $param1;
                $xml = file_get_contents( $param2 );
                if( false === $xml )
                {
                    throw new Exception( "Failed to locate parameter xml file: '" . $param2 . "'" );
                }
                $dom = new DOMDocument();
                $dom->preserveWhiteSpace = false;
                $loadResult = $dom->loadXML( $xml );
                if( false === $loadResult )
                {
                    throw new Exception( "XML file '" . $param2 . "' does not contain valid XML" );
                }
                $xpath = new DOMXPath( $dom );
                AttributeFunctions::updateAttribute( $classIdentifier, $xpath );
                break;

            case self::attribute_setfield:
                $classIdentifier = $param1;
                $attributeIdentifier = $param2;
                $fieldIdentifier = $param3;
                $fieldValue = $param4;
                AttributeFunctions::setField( $classIdentifier, $attributeIdentifier, $fieldIdentifier, $fieldValue );
                break;

            case self::attribute_info:
                $classIdentifier = $param1;
                $attributeIdentifier = $param2;
                $fieldIdentifier = $param3;
                AttributeFunctions::info( $classIdentifier, $attributeIdentifier, $fieldIdentifier );
                break;

            case self::attribute_migrate:
                $classIdentifier = $param1;
                $srcAttribute = $param2;
                $conversion = $param3;
                $destAttribute = $param4;
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                $this->attribute_migrate( $classIdentifier, $srcAttribute, $conversion, $destAttribute );
                break;

            case self::attribute_fromstring:
                $contentObjectId = $param1;
                $attributeIdentifier = $param2;
                $newValue = $param3;
                AttributeFunctions::fromString( $contentObjectId, $attributeIdentifier, $newValue );
                break;

            case self::attribute_tostring:
                $contentObjectId = $param1;
                $attributeIdentifier = $param2;
                echo AttributeFunctions::toString( $contentObjectId, $attributeIdentifier ) . "\n";
                break;
            
            case self::attribute_createalias:
                $contentObjectId = $param1;
                $attributeIdentifier = $param2;
                $aliasName = $param3;
                echo AttributeFunctions::createAlias( $contentObjectId, $attributeIdentifier, $aliasName ) . "\n";
                break;

            case self::attribute_contentobjectid:
                $contentObjectAttributeId = $param1;
                $version = ( $param2 )? $param2 : 1;
                echo AttributeFunctions::contentobjectid( $contentObjectAttributeId, $version ) . "\n";
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new attribute_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>