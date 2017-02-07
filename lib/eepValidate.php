<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
class eepValidate
{
    /*
    TIPS:

    Do most validations in the functions, not in the switch.

    validate content class identifier:
    ----------------------------------
    $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
    if( !$contentClass )
        throw new Exception( "This content class does not exist: [" . $classIdentifier . "]" );

    validate content class attribute:
    ---------------------------------
    $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
    if( !$contentClass )
        throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

    $classDataMap = $contentClass->attribute( "data_map" );
    if( !isset( $classDataMap[ $attributeIdentifier ] ) )
        throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $attributeIdentifier . "]" );

    content object id:
    ------------------
    if( !eepValidate::validateContentObjectId( $objectId ) )
        throw new Exception( "This is not an object id: [" .$objectId. "]" );

    node id:
    ------------------
    if( !eepValidate::validateContentNodeId( $nodeId ) )
        throw new Exception( "This is not an node id: [" .$nodeId. "]" );

    */

    //--------------------------------------------------------------------------
    static function validateContentObjectId( $id )
    {
        if( is_numeric($id) && 0 < (integer )$id )
        {
            $id = (integer )$id;
            if( eZContentObject::fetch( $id, false ) )
            {
                return true;
            }
        }
        return false;
    }

    //--------------------------------------------------------------------------
    static function validateContentNodeId( $id )
    {
        if( is_numeric($id) && 0 < (integer )$id )
        {
            $id = (integer )$id;
            if( eZContentObjectTreeNode::fetch( $id, false, false, false ) )
            {
                return true;
            }
        }
        return false;
    }
}
