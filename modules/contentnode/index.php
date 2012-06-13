<?php
/**
 * eep/modules/contentnode/index.php
 */

class contentnode_commands
{
    const contentnode_clearsubtreecache  = "clearsubtreecache";
    const contentnode_contentobject      = "contentobject";
    const contentnode_info               = "info";
    const contentnode_location           = "location";
    const contentnode_find               = "find";
    const contentnode_deletesubtree      = "deletesubtree";
    const contentnode_move               = "move";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentnode_clearsubtreecache
        , self::contentnode_contentobject
        , self::contentnode_deletesubtree
        , self::contentnode_find
        , self::contentnode_info
        , self::contentnode_location
        , self::contentnode_move
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
clearsubtreecache

contentobject
- convert a content node id to a content object id
  eep use ezroot <path>
  eep contentnode contentobject <content node id>
  or
  eep use ezroot <path>
  eep use contentnode <content node id>
  eep contentnode contentobject

deletesubtree
- is hardcoded to use user 14 to do the deletions
- supports --limit=N to override the sanity check limit (0 means no-limit)
  eep use ezroot <path>
  eep contentnode deletesubtree <subtree node id>
  ... or
  eep use contentnode <subtree node id>
  eep contentnode deletesubtree
  
find
- supports --limit=N and/or --offset=M
  eep use ezroot <path>
  eep cn find <content class> <parent node id> <search string>P  

info
  eep use ezroot <path>
  eep use contentnode <node id>
  eep contentnode info
  ... or
  eep use ezroot <path>
  eep contentnode info <node id>

location
- put content object at an additional location
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentnode location <new parent node id>
  
move
- move provided node to be child at new location
  eep use ezroot <path>
  eep use contentnode <node id>
  eep contentnode move <new parent node id>
  or
  eep contentnode move <node id> <new parent node id>
EOT;
    }

    //--------------------------------------------------------------------------
    private function deleteSubtree( $subtreeNodeId, $additional )
    {
        $effectiveLimit = eepSetting::DeleteNodesSanityCheck;
        if( isset($additional["limit"]) )
        {
            $effectiveLimit = $additional["limit"];
        }
        
        if( !eepValidate::validateContentNodeId( $subtreeNodeId ) )
            throw new Exception( "This is not an node id: [" .$subtreeNodeId. "]" );
            
        // need to operate in a privileged account
        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        $adminUserObject->loginCurrent();
        // get the name, just for display purposes
        $subtreeNode = eZContentObjectTreeNode::fetch( $subtreeNodeId );
        $subtreeName = $subtreeNode->getName();
    
        $subtreeCount = eZContentObjectTreeNode::subTreeCountByNodeID( array(), $subtreeNodeId );
        if( ($subtreeCount > $effectiveLimit ) && (0 != $effectiveLimit) )
        {
            echo "The number of subitems [" .$subtreeCount. "] exceeds the sanity check: [" .$effectiveLimit. "]\n";
            return false;
        }
        
        /*
        // test that we can delete the subtree
        $info = eZContentObjectTreeNode::subtreeRemovalInformation( array($subtreeNodeId) );
        if( !$info[ "can_remove_all" ] )
        {
            var_dump($info);
            echo "Permission is denied for the '" . $subtreeName . "' subtree [nodeid=". $subtreeNodeId . "]\n";
            return false; // not that anyone is checking the return value ...
        }
        */

        // do the removal
        $removeResult = eZContentObjectTreeNode::removeSubtrees( array($subtreeNodeId), false, false );
        if( true === $removeResult )
        {
            echo "Subtree '" .$subtreeName. "' removed ok\n";
        }
        else
        {
            echo "Failed to remove subtree\n";
        }
        $adminUserObject->logoutCurrent();
    }
        
    //--------------------------------------------------------------------------
    private function fetchNodeInfoFromId( $nodeId )
    {
        if( !eepValidate::validateContentNodeId( $nodeId ) )
            throw new Exception( "This is not an node id: [" .$nodeId. "]" );
        
        $keepers = array
        (
            "Name"
            , "ContentObjectID"
            , "MainNodeID"
            , "ClassIdentifier"
            , "PathIdentificationString"
            , "PathString"
            , "ParentNodeID"
            , "CurrentLanguage"
            , "ContentObjectVersion"
            , "RemoteID"
            , "IsHidden"
            , "IsInvisible"
            , "ContentObjectIsPublished"
        );
        // get the node
        $node = eZContentObjectTreeNode::fetch( $nodeId );
        
        //var_dump($node);
        
        // extract the members we want
        $results[] = array( "key", "value" );
        foreach( $keepers as $key )
        {
            $results[] = array( $key, $node->$key );
        }
        // additional info
        $results[] = array( "Reverse related count", eZContentObjectTreeNode::reverseRelatedCount( array($nodeId) ) );
        $params = array
        (
            'Depth' => 1
            , 'DepthOperator' => 'eq'
            , 'Limitation' => array()
        );
        $results[] = array( "Children count", eZContentObjectTreeNode::subTreeCountByNodeID( $params, $nodeId ) );
        $results[] = array( "URL Alias", $node->urlAlias() );
        // do output
        eep::printTable( $results, "contentnode info [" .$nodeId. "]" );
    }

    //--------------------------------------------------------------------------
    // CURRENTLY UNUSED
    private function searchForNodes_byAttribute( $parentNodeId, $classIdentifier, $attributeIdentifier, $op, $searchString )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "This content class does not exist: [" . $classIdentifier . "]" );

        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is an invalid parent node id: [" .$parentNodeId. "]" );

        $classDataMap = $contentClass->attribute( "data_map" );
        if( !isset( $classDataMap[ $attributeIdentifier ] ) )
            throw new Exception( "Content class '" . $classIdentifier . "' does not contain this attribute: [" . $attributeIdentifier . "]" );
        
        $qualifiedAttributeName = $classIdentifier ."/". $attributeIdentifier;

        $limit = 100;
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = 0;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }
        
        $params[ "ClassFilterType" ] = "include";
        $params[ "ClassFilterArray" ] = array( $classIdentifier );
        //$params[ "Depth" ] = 1;
        $params[ "MainNodeOnly" ] = true;
        $params[ "IgnoreVisibility" ] = true;
        $params[ 'Limitation' ] = array();
        $params[ 'Offset' ] = $offset;
        $params[ 'Limit' ] = $limit;

        switch( $op )
        {
            default:
                throw new Exception( "in contentobject_commands:searchForObjects_byAttribute(), comparison operator [" .$op. "] is not recognized" );
                break;
            
            case "=":
                $params[ "AttributeFilter" ] = array
                (
                    array( $qualifiedAttributeName, "=", $searchString )
                );
                break;
            
            case "like":
                $params[ "AttributeFilter" ] = array
                (
                    "and"
                    , array( $qualifiedAttributeName, "like", "*".$searchString."*" )
                );
                break;
            
            case "!=":
                $params[ "AttributeFilter" ] = array
                (
                    array( $qualifiedAttributeName, "!=", $searchString )
                );
                break;
        }        
        
        $matches = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentNodeId );
        $title = "Search on '" .$qualifiedAttributeName. "' for '" .$op." ".$searchString. "' from parent ".$parentNodeId;
        eep::displayNodeList( $matches, $title );
    }

    //--------------------------------------------------------------------------
    private function searchForNodes( $parentNodeId, $classIdentifier, $searchString )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "This content class does not exist: [" . $classIdentifier . "]" );
            
        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is not an node id: [" .$parentNodeId. "]" );
        
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $attributeList = eZContentClassAttribute::fetchListByClassID( $classId );

        $limit = 100;
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = 0;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }

        $params[ "ClassFilterType" ] = "include";
        $params[ "ClassFilterArray" ] = array( $classIdentifier );
        $params[ "MainNodeOnly" ] = true;
        $params[ "IgnoreVisibility" ] = true;
        $params[ 'Limitation' ] = array();
        $params[ 'Offset' ] = $offset;
        $params[ 'Limit' ] = $limit;
                
        $searchStringIsNumeric = is_numeric( $searchString );
        $numericTypes = array
        (
            "ezinteger"
            , "ezfloat"
            , "ezdatetime"
            , "ezboolean"
        );
        
        $params[ "AttributeFilter" ] = array( "or" );
        foreach( $attributeList as $attribute )
        {
            $qualifiedAttributeName = $classIdentifier ."/". $attribute->Identifier; // eg folder/name
            // if the string is not numeric and the attribute is numeric, don't
            // search on it -- this is still rough functionality
            if( in_array( $attribute->DataTypeString, $numericTypes )
                && !$searchStringIsNumeric )
            {
                continue;
            }
            // search on everything else -- maybe a bad idea which generates too
            // many hits ...
            $params[ "AttributeFilter" ][] = array( $qualifiedAttributeName, "like", "*".$searchString."*" );
        }
        
        $matches = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentNodeId );        
        $title = "Search on all attributes in '" .$classIdentifier. "' for '".$searchString. "' from parent ".$parentNodeId;
        eep::displayNodeList( $matches, $title );
    }
    
    //--------------------------------------------------------------------------
    private function location( $objectId, $parentNodeId )
    {
        if( !eepValidate::validateContentObjectId( $objectId ) )
            throw new Exception( "This is not an object id: [" .$objectId. "]" );
        
        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is not an node id: [" .$parentNodeId. "]" );
        
        $object = eZContentObject::fetch( $objectId );
        $object->addLocation( $parentNodeId );

        // this is a guess; but otherwise, the new node doesn't become available
        eep::republishObject( $objectId );
    }
    
    //--------------------------------------------------------------------------
    private function convertToContentObjectId( $nodeId )
    {
        $object = eZContentObject::fetchByNodeID( $nodeId, false );
        return $object[ "id" ];
    }
    
    //--------------------------------------------------------------------------
    private function move( $nodeId, $parentNodeId )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeId );
        $node->move( $parentNodeId );
    }

    //--------------------------------------------------------------------------
    private function clearSubtreeCache( $nodeId )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeId );

        $limit = 50;
        $offset = 0;
        $params = array( 'AsObject' => false,
                         'Depth' => false,
                         'Limitation' => array() ); // Empty array means no permission checking
        $subtreeCount = $node->subTreeCount( $params );
        while ( $offset < $subtreeCount )
        {
            $params['Offset'] = $offset;
            $params['Limit'] = $limit;
            $subtree = $node->subTree( $params );
            $offset += count( $subtree );
            if ( count( $subtree ) == 0 )
            {
                break;
            }
            $objectIDList = array();
            foreach ( $subtree as $subtreeNode )
            {
                $objectIDList[] = $subtreeNode['contentobject_id'];
            }
            $objectIDList = array_unique( $objectIDList );
            unset( $subtree );

            foreach ( $objectIDList as $objectID )
                eZContentCacheManager::clearContentCacheIfNeeded( $objectID );
        }

    }

    //--------------------------------------------------------------------------
    public function run( $argv, $additional )
    {
        $command = @$argv[2];
        $param1 = @$argv[3];
        $param2 = @$argv[4];
        $param3 = @$argv[5];

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
            
            case self::contentnode_info:
                $nodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $nodeId = $param1;
                }
                $this->fetchNodeInfoFromId( $nodeId );
                break;
            
            case self::contentnode_location:
                $contentObjectId = $eepCache->readFromCache( eepCache::use_key_object );
                $parentNodeId = $param1;
                $this->location( $contentObjectId, $parentNodeId );
                break;
            
            case self::contentnode_find:
                $classIdentifier = $param1;
                $parentNodeId = $param2;
                $searchString = $param3;
                $this->searchForNodes( $parentNodeId, $classIdentifier, $searchString );
                break;
            
            case self::contentnode_deletesubtree:
                $subtreeNodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $subtreeNodeId = $param1;
                }
                $this->deleteSubtree( $subtreeNodeId, $additional );
                break;
            
            case self::contentnode_contentobject:
                $nodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $nodeId = $param1;
                }
                echo $this->convertToContentObjectId( $nodeId );
                break;
            
            case self::contentnode_move:
                if( $param2 )
                {
                    // both the node and the parent are being provided
                    $nodeId = $param1;
                    $parentNodeId = $param2;
                }
                else
                {
                    $nodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                    $parentNodeId = $param1;
                }
                $this->move( $nodeId, $parentNodeId );
                break;
            
            case self::contentnode_clearsubtreecache:
                $nodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $nodeId = $param1;
                }
                echo $this->clearSubtreeCache( $nodeId );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new contentnode_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>