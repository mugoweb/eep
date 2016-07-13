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

    <!-- supported: ezstring ezobjectrelationlist ezinteger ezselection ezxmltext ezimage eztags and probably others -->
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

        <eztags>
             <subtree>0</subtree>
             <hideroot>1</hideroot>
             <dropdown>0</dropdown>
             <maxtags>0</maxtags>
        </eztags>

        <!-- not fully supported
        <ezmatrix>
            <default_row_count>
                3
            </default_row_count>
        </ezmatrix>
        -->
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

    //--------------------------------------------------------------------------
    // this is the entry point for creating a new attribute    
    static public function updateAttribute( $classIdentifier, $newAttributeXPath )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        $newAttributeIdentifier = trim( $newAttributeXPath->query( "//newattribute/identifier" )->item( 0 )->nodeValue );

        $classDataMap = $contentClass->attribute('data_map' );
        if( !isset( $classDataMap[ $newAttributeIdentifier ] ) )
        {
            // attribute is not set in the class, so add it to the class
            $classAttributeID = AttributeFunctions::addAttributeToClass( $contentClass, $newAttributeXPath );
            // update all the objects with the new attribute
            AttributeFunctions::updateContentObjectAttributes( $contentClass, $classAttributeID, $newAttributeIdentifier );
        }
        else
        {
            // in case we're repairing an attribute that was added in the ui and
            // timed out before all the objects were updated
            $classAttributeID = $classDataMap[ $newAttributeIdentifier ]->attribute( 'id' );
            AttributeFunctions::updateContentObjectAttributes( $contentClass, $classAttributeID, $newAttributeIdentifier );
        }
    }

    //--------------------------------------------------------------------------
    // note that $contentClass is the return from:
    // $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
    //
    static function addAttributeToClass( $contentClass, $newAttributeXPath )
    {
        $classID = $contentClass->attribute( "id" );
        
        // extracting from the xml, it's gross, but it's better than from an assoc array
        $xmlValues = array
        (
            "identifier"                    => trim( $newAttributeXPath->query( "//newattribute/identifier" )->item( 0 )->nodeValue )
            , "display_name"                => trim( $newAttributeXPath->query( "//newattribute/displayname" )->item( 0 )->nodeValue )
            , "description"                 => trim( $newAttributeXPath->query( "//newattribute/description" )->item( 0 )->nodeValue )
            , "language"                    => trim( $newAttributeXPath->query( "//newattribute/language" )->item( 0 )->nodeValue )
            , "can_translate"               => trim( $newAttributeXPath->query( "//newattribute/can_translate" )->item( 0 )->nodeValue )
            , "is_required"                 => trim( $newAttributeXPath->query( "//newattribute/is_required" )->item( 0 )->nodeValue )
            , "is_searchable"               => trim( $newAttributeXPath->query( "//newattribute/is_searchable" )->item( 0 )->nodeValue )
            , "is_information_collector"    => trim( $newAttributeXPath->query( "//newattribute/is_information_collector" )->item( 0 )->nodeValue )
            , "datatypestring"              => trim( $newAttributeXPath->query( "//newattribute/datatypestring" )->item( 0 )->nodeValue )
            , "content"                     => trim( $newAttributeXPath->query( "//newattribute/content" )->item( 0 )->nodeValue )
        );
        
        // create new attribute
        $attributeCreationInfo = array
        (
            "identifier"                    => $xmlValues[ "identifier" ]
            , "serialized_name_list"        => serialize( array(
                                                            $xmlValues[ "language" ]    => $xmlValues[ "display_name" ]
                                                            , "always-available"        => $xmlValues[ "language" ]
                                                        ) ) 
            , "description"                 => $xmlValues[ "description" ]
            , "can_translate"               => $xmlValues[ "can_translate" ]
            , "is_required"                 => $xmlValues[ "is_required" ]
            , "is_searchable"               => $xmlValues[ "is_searchable" ]
            , "is_information_collector"    => $xmlValues[ "is_information_collector" ]
        );

        $newAttribute = eZContentClassAttribute::create( $classID, $xmlValues[ "datatypestring" ], $attributeCreationInfo  );
        $dataType = $newAttribute->dataType();
        if( !$dataType )
        {
            throw new Exception( "Unknown datatype: [ " .$datatype. " ]" );
        }
        $dataType->initializeClassAttribute( $newAttribute );
        $newAttribute->store();
        AttributeFunctions::updateParameters( $newAttribute, $newAttributeXPath );
        $newAttribute->sync();

        $content = $xmlValues[ "content" ];
        if( "eep-no-content" != $content )
        {
            $newAttribute->setContent( $content );
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
    static function updateParameters( $classAttribute, $newAttributeXPath )
    {
        switch( $classAttribute->DataTypeString )
        {
            case "ezstring":
                $maxStringLength = (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezstring/maxstringlength" )->item( 0 )->nodeValue );
                if( $maxStringLength < 1 )
                {
                    $maxStringLength = 1;
                }
                elseif( $maxStringLength > 255 )
                {
                    $maxStringLength = 255;
                }
                $classAttribute->setAttribute( "data_int1", $maxStringLength );
                break;

            case "ezxmltext":
                $numberOfLines = (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezxmltext/numberoflines" )->item( 0 )->nodeValue );
                if( $numberOfLines < 1 )
                {
                    $numberOfLines = 1;
                }
                elseif( $numberOfLines > 30 )
                {
                    $numberOfLines = 30;
                }
                $classAttribute->setAttribute( "data_int1", $numberOfLines );
                break;
            
            case "ezselection":
                // ripped off from kernel/classes/datatypes/ezselection/ezselectiontype.php
                // build the internal XML representation of the options list, first, build a new xml doc
                $doc = new DOMDocument( '1.0', 'utf-8' );
                $root = $doc->createElement( "ezselection" );
                $doc->appendChild( $root );
                $options = $doc->createElement( "options" );
                $root->appendChild( $options );
                // then loop through all the options specified in the new attribute xml and update the xml doc
                $nodeList = $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezselection/options" );
                $optionsNode = $nodeList->item( 0 );
                $numberOfOptions = $optionsNode->childNodes->length;
                if( 1 < $numberOfOptions )
                {
                    for( $m=0; $m<$numberOfOptions; $m+=1 )
                    {
                        $optionNode = $optionsNode->childNodes->item( $m );
                        //echo "child value:" . trim( $optionNode->nodeValue ) . "\n";
                        $eZOptionNode = $doc->createElement( "option" );
                        $eZOptionNode->setAttribute( 'id', $m );
                        $eZOptionNode->setAttribute( 'name', trim( $optionNode->nodeValue ) );
                        $options->appendChild( $eZOptionNode );
                    }
                }
                // save the options data
                $eZXML = $doc->saveXML();
                $classAttribute->setAttribute( "data_text5", $eZXML );
                // set multi-select versus single selection
                if( 0 == trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezselection/is_multi_select" )->item( 0 )->nodeValue ) )
                {
                    $classAttribute->setAttribute( "data_int1", 1 );
                }
                else
                {
                    $classAttribute->setAttribute( "data_int1", 0 );                
                }
                break;
            
            case "ezboolean":
                $defaultValue = trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezboolean/default_value" )->item( 0 )->nodeValue );
                if( "eep-no-content" != $defaultValue )
                {
                    $classAttribute->setAttribute( "data_int3", $defaultValue );
                }
                break;

            case "ezobjectrelation":
                {
                    $content = $classAttribute->content();
                    // extract the xml
                    $xmlValues = array
                    (
                        "selection_type"            => trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezobjectrelation/selection_type" )->item( 0 )->nodeValue )
                        , "fuzzy_match"             => trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezobjectrelation/fuzzy_match" )->item( 0 )->nodeValue )
                        , "default_selection_node"  => trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/ezobjectrelation/default_selection_node" )->item( 0 )->nodeValue )
                    );
                    
                    $content[ "selection_type" ] = $xmlValues[ "selection_type" ];
                    
                    $content[ "fuzzy_match" ] = false;
                    if( "false" != $xmlValues[ "fuzzy_match" ] )
                    {
                        $content[ "fuzzy_match" ] = true;
                    }
                    
                    $content[ "default_selection_node" ] = false;
                    if( "eep-no-content" != $xmlValues[ "default_selection_node" ] )
                    {
                        if( is_numeric( $xmlValues[ "default_selection_node" ] ) )
                        {
                            $content[ "default_selection_node" ] = $xmlValues[ "default_selection_node" ];
                        }
                        else
                        {
                            $node = eZContentObjectTreeNode::fetchByURLPath( $xmlValues[ "default_selection_node" ] );
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

            case "eztags":
                $classAttribute->setAttribute( eZTagsType::SUBTREE_LIMIT_FIELD, (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/eztags/subtree" )->item( 0 )->nodeValue ) );
                $classAttribute->setAttribute( eZTagsType::SHOW_DROPDOWN_FIELD, (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/eztags/dropdown" )->item( 0 )->nodeValue ) );
                $classAttribute->setAttribute( eZTagsType::HIDE_ROOT_TAG_FIELD, (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/eztags/hideroot" )->item( 0 )->nodeValue ) );
                $classAttribute->setAttribute( eZTagsType::MAX_TAGS_FIELD, (integer )trim( $newAttributeXPath->query( "//newattribute/additional_for_specific_datatype/eztags/maxtags" )->item( 0 )->nodeValue ) );
                break;

            case "ezmatrix":
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
                /*
                note that $params is wrong, and has been replaced by the xpath data
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
                */
                break;

            default:
                break;
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
        $countProcessed = 0;
        $batchSize = 200;
        $totalObjectCount = eZContentObject::fetchSameClassListCount( $classId );
        for( $offset=0; $offset<$totalObjectCount; $offset+=$batchSize )
        {
            $objects = eZContentObject::fetchSameClassList( $classId, false, $offset, $batchSize );
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
                    $countProcessed += 1;
                }
                echo "Percent complete: " . sprintf( "% 3.2f", ( $countProcessed / $totalObjectCount ) * 100.0 ) . "%\r";
            }
            unset( $GLOBALS[ "eZContentObjectContentObjectCache" ] );
            unset( $GLOBALS[ "eZContentObjectDataMapCache" ] );
            unset( $GLOBALS[ "eZContentObjectVersionCache" ] );
            unset( $objects );
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
        $countProcessed = 0;
        $offset = 0;
        $limit = 500;
        $fetchParameters = array
        (
            "ClassFilterType"       => "include"
            , "ClassFilterArray"    => array( $classIdentifier )
            , "MainNodeOnly"        => true
            , "IgnoreVisibility"    => true
            , "Offset"              => $offset
            , "Limit"               => $limit
        );
        $batch = eZContentObjectTreeNode::subTreeByNodeID( $fetchParameters, 1 );
        while( 0 < count( $batch ) )
        {
            //echo "found: " . count($batch) . " onix_product instances at offset: " . $fetchParameters[ "Offset" ] . "\n";    
            foreach( $batch as $n => $node )
            {
                $dm = $node->ContentObject->DataMap();
                if( isset( $dm[ $attributeIdentifier ] ) )
                {
                    $attr = $dm[ $attributeIdentifier ];
                    $attrId = $attr->ID;
                    $attr->removeThis( $attrId );
                    //echo "removed";
                }
                else
                {
                    //echo "skipped"; // because, one presumes, it was previously removed
                }
                $countProcessed += 1;
                //echo "Percent complete: " . sprintf( "% 3.3f", (($num+1) / $numObjects)*100.0 ) . "%\r";
                echo "Number processed: " . sprintf( "%08d", $countProcessed ) . "\r";
            }
            unset( $GLOBALS[ "eZContentObjectContentObjectCache" ] );
            unset( $GLOBALS[ "eZContentObjectDataMapCache" ] );
            unset( $GLOBALS[ "eZContentObjectVersionCache" ] );
            $fetchParameters[ "Offset" ] += $limit;
            $batch = eZContentObjectTreeNode::subTreeByNodeID( $fetchParameters, 1 );
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
        echo "Number processed: " . sprintf( "%08d", $countProcessed ) . " and completed.\n";
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
    public static function convertInputIntoEZXML( $input, $parseLineBreaks = false )
    {
        // repair neutered HTML ...
        $input = str_replace( array( "&lt;", "&gt;", "&amp;" ), array( "<", ">", "&" ), $input );

        // deal with manual linebreaks added for formatting
        // set restoration markers ...
        // for two consecutive newline characters or carriage return followed by newline i.e. empty lines
        $input = str_replace( array( "\n\n", "\r\n\r\n" ), '__DOUBLE_NL_REST__', $input );
        // kill any remaining newline/carriage return characters
        $input = str_replace( array( "\n", "\r" ), ' ', $input );
        // replace restoration markers with newline(s)
        $input = str_replace( "__DOUBLE_NL_REST__", "\n\n", $input );

        // and convert it into ezxml
        // params used: validateErrorLevel, detectErrorLevel, parseLineBreaks
        $parser = new eZOEInputParser( eZXMLInputParser::ERROR_NONE, eZXMLInputParser::ERROR_NONE, $parseLineBreaks );
        $document = $parser->process( $input );
        $output = eZXMLTextType::domString( $document );

        return $output;
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

        if( "ezxmltext" == $dataMap[ $attributeIdentifier ]->DataTypeString )
        {
            // convert passed value to ez xml format
            $value = AttributeFunctions::convertInputIntoEZXML( $value );
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
    public static function setAttribute( $contentObjectId, $attributeIdentifier, $attributeValue )
    {
        $contentObject = eZContentObject::fetch( $contentObjectId );

        if ( !$contentObject )
        {
            throw new Exception( "This is not a content object [" . $contentObjectId . "]" );
        }

        if ( !$contentObject->hasAttribute( $attributeIdentifier ) )
        {
            throw new Exception( "This is not a content object attribute identifier [" . $attributeIdentifier . "]" );   
        }

        $contentObject->setAttribute( $attributeIdentifier, $attributeValue );
        $contentObject->store();
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

    //--------------------------------------------------------------------------
    public static function contentobjectid( $contentObjectAttributeId, $version = 1 )
    {
        $db = eZDB::instance();
        $query = 'SELECT `contentobject_id` FROM `ezcontentobject_attribute`';
        $query .= ' WHERE `ezcontentobject_attribute`.`id` = ' . (integer) $contentObjectAttributeId;
        $query .= ' AND `ezcontentobject_attribute`.`version` = ' . (integer) $version;
        $query .= ' LIMIT 1';
        $result = $db->arrayQuery( $query );

        if($result)
        {
            return $result[0]['contentobject_id'];
        }
    }
}
?>
