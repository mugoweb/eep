<?php
/**
 * eep/modules/contentclass/index.php
 */

class contentclass_commands
{
    const contentclass_deleteclass          = "deleteclass";
    const contentclass_listattributes       = "listattributes";
    const contentclass_fetchallinstances    = "fetchallinstances";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentclass_listattributes
        , self::contentclass_deleteclass
        , self::contentclass_fetchallinstances
    );
    var $help = "";                     // used to dump the help string
        
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
deleteclass
- deletes all the instances of a class, and then deletes the class itself
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep contentclass deleteclass
  or
  eep use ezroot <path>
  eep contentclass deleteclass <class identifier>

listattributes
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep contentclass listattributes

fetchallinstances
  - note that this supports limit and offset parameters
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep contentclass fetchallinstances
  - or -
  eep contentclass fetchallinstances <content class identifier>
EOT;
    }
    
    //--------------------------------------------------------------------------
    // delete a content class and all the objects that use it
    // see: eZContentClassOperations::remove( $classID )
    private function deleteClass( $classIdentifier )
    {
        $chunkSize = 1000;
        
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );

        $contentClass = eZContentClass::fetch( $classId );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
        
        $totalObjectCount = eZContentObject::fetchSameClassListCount( $classId );
        echo "Deleting " . $totalObjectCount . " objects.\n";
        
        $moreToDelete = 0 < $totalObjectCount;
        $totalDeleted = 0;

        // need to operate in a privileged account - use doug@mugo.ca
        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        $adminUserObject->loginCurrent();
        
        while( $moreToDelete )
        {
            $params[ "IgnoreVisibility" ] = true;
            $params[ 'Limitation' ] = array();
            $params[ 'Limit' ] = $chunkSize;
            $params[ 'ClassFilterType' ] = "include";
            $params[ 'ClassFilterArray' ] = array( $classIdentifier );
            $children = eZContentObjectTreeNode::subTreeByNodeID( $params, 2 );

            foreach( $children as $child )
            {
                $info = eZContentObjectTreeNode::subtreeRemovalInformation( array($child->NodeID) );
                
                if( !$info[ "can_remove_all" ] )
                {
                    $msg = " permission is denied for nodeid=".$child->NodeID;
                    // todo, this can yield an infinite loop if some objects are
                    // not deleteable, but you don't take that number into account
                    // at the bottom of the loop - where there will always be
                    // some >0 number of undeleteable objects left
                    echo $msg . "\n";
                    continue;
                }

                $removeResult = eZContentObjectTreeNode::removeSubtrees( array($child->NodeID), false, false );
                if( true === $removeResult )
                {
                    $totalDeleted += 1;
                }
                else
                {
                    $msg = " failed to delete nodeid=".$child->NodeID;
                    echo $msg . "\n";
                }

                echo "Percent complete: " . sprintf( "% 3.3f", ($totalDeleted / $totalObjectCount)*100.0  ) . "%\r";
                unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
                unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
                unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
            }
            $moreToDelete = 0 < eZContentObject::fetchSameClassListCount( $classId );
        }
        echo "\nDone deleting objects.\n";
        
        $adminUserObject->logoutCurrent();
                
        eZContentClassClassGroup::removeClassMembers( $classId, 0 );
        eZContentClassClassGroup::removeClassMembers( $classId, 1 );

        // Fetch real version and remove it
        $contentClass->remove( true );
        
        // this seems to mainly cause an exception, might be an idea to simply skip it
        // Fetch temp version and remove it
        $tempDeleteClass = eZContentClass::fetch( $classId, true, 1 );
        if( $tempDeleteClass != null )
            $tempDeleteClass->remove( true, 1 );
    }
    
    //--------------------------------------------------------------------------
    private function fetchallinstances( $classIdentifier, $additional )
    {
        $limit = false;
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = false;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }
        
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $allInstances = eZContentObject::fetchSameClassList( $classId, false, $offset, $limit );
        $title = "All instances of content class '" . $classIdentifier . "'";
        eep::displayNonObjectList( $allInstances, $title );
    }
    
    //--------------------------------------------------------------------------
    public function run( $argv, $additional )
    {
        $command = @$argv[2];
        $param1 = @$argv[3];
        $param2 = @$argv[4];

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
            
            case self::contentclass_listattributes:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                AttributeFunctions::listAttributes( $classIdentifier );
                break;
            
            case self::contentclass_deleteclass:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                $this->deleteClass( $classIdentifier );
                break;

            case self::contentclass_fetchallinstances:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                $this->fetchallinstances( $classIdentifier, $additional );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new contentclass_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>