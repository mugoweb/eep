<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/contentclass/index.php
 */

class contentclass_commands
{
    const contentclass_createclass          = "createclass";
    const contentclass_deleteclass          = "deleteclass";
    const contentclass_listattributes       = "listattributes";
    const contentclass_setclassobjectidentifier   = "setclassobjectidentifier";
    const contentclass_setiscontainer       = "setiscontainer";
    const contentclass_fetchallinstances    = "fetchallinstances";
    const contentclass_appendtogroup        = "appendtogroup";
    const contentclass_removefromgroup      = "removefromgroup";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentclass_createclass
        , self::contentclass_deleteclass
        , self::contentclass_listattributes
        , self::contentclass_setclassobjectidentifier
        , self::contentclass_setiscontainer
        , self::contentclass_fetchallinstances
        , self::contentclass_appendtogroup
        , self::contentclass_removefromgroup
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
createclass
- create a stub content class with an automatic content class identifier and
  default string for object-naming; uses the "admin" user to create
  the class; returns the class identifier so that attributes can then be added
  and the default naming be updated
  eep createclass <Display name> <Content class group identifier>

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
  
setclassobjectidentifier
- set the string used to name instances of the class, uses the same syntax as in
  the admin ui
  eep contentclass setclassobjectidentifier <class identifier> <object naming string or pattern>

setiscontainer
- set or unset the 'is container' flag on the class
  eep contentclass setiscontainer <class identifier> <0|1>
  
fetchallinstances
  - note that this supports limit and offset parameters
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep contentclass fetchallinstances
  - or -
  eep contentclass fetchallinstances <content class identifier>

appendtogroup
   eep use ezroot <path>
   eep contentclass appendtogroup <content class identifier> <group identifier>

removefromgroup
   eep use ezroot <path>
   eep contentclass removefromgroup <content class identifier> <group identifier>
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
    private function appendToGroup( $classIdentifier, $groupIdentifier )
    {
        $classObject = eZContentClass::fetchByIdentifier( $classIdentifier );
        $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier );
        if( !$classObject )
        {
            throw new Exception( "Invalid Class Identifier. [" .$classIdentifier. "]" );
        }
        if( !$groupObject )
        {
            throw new Exception( "Invalid Group Identifier. [" .$groupIdentifier. "]" );
        }

        if( $groupObject->appendClass($classObject) )
        {
            echo "Successfully appended class [" .$classIdentifier. "] to group [" .$groupIdentifier. "]";
            return;
        }
        else
        {
            throw new Exception( "Unknown error occurred" );
        }
    }

    //--------------------------------------------------------------------------
    private function removeFromGroup( $classIdentifier, $groupIdentifier )
    {
        $classObject = eZContentClass::fetchByIdentifier( $classIdentifier );
        $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier );
        if( !$classObject )
        {
            throw new Exception( "Invalid Class Identifier. [" .$classIdentifier. "]" );
        }
        if( !$groupObject )
        {
            throw new Exception( "Invalid Group Identifier. [" .$groupIdentifier. "]" );
        }

        $db = eZDB::instance();
        $db->begin();
        eZContentClassClassGroup::removeGroup( $classObject->ID, null, $groupObject->ID );
        $db->commit();
        echo "Successfully removed class [" .$classIdentifier. "] from group [" .$groupIdentifier. "]";
    }
    
    //--------------------------------------------------------------------------
    private function createClass( $displayName, $classIdentifier, $groupIdentifier, $groupId )
    {
        $adminUserObject = eZUser::fetchByName( "admin" );
        $adminUserObject->loginCurrent();
        $adminUserId = $adminUserObject->attribute( 'contentobject_id' );
        $language = eZContentLanguage::topPriorityLanguage();
        $editLanguage = $language->attribute( 'locale' );
        $class = eZContentClass::create( $adminUserId, array(), $editLanguage );
        // this is the display name, ez automatically creates the content-class-identifier from it
        $class->setName( $displayName, $editLanguage );
        $class->setAttribute( "identifier", $classIdentifier );
        // default naming for objects - content classes should update this value once they have attributes added
        $class->setAttribute( 'contentobject_name', 'eep-created-content-class' );
        $class->store();
        $editLanguageID = eZContentLanguage::idByLocale( $editLanguage );
        $class->setAlwaysAvailableLanguageID( $editLanguageID );
        $ClassID = $class->attribute( 'id' );
        $ClassVersion = $class->attribute( 'version' );
        $ingroup = eZContentClassClassGroup::create( $ClassID, $ClassVersion, $groupId, $groupIdentifier );
        $ingroup->store();
        // clean up the content class status
        $class->storeDefined( array() );
        $adminUserObject->logoutCurrent();
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

            case self::contentclass_appendtogroup:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                if( $param2 )
                {
                    $groupIdentifier = $param2;
                }
                else
                {
                    $groupIdentifier = null;
                }
                $this->appendToGroup( $classIdentifier, $groupIdentifier );
                break;
            
            case self::contentclass_removefromgroup:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                if( $param2 )
                {
                    $groupIdentifier = $param2;
                }
                else
                {
                    $groupIdentifier = null;
                }
                $this->removeFromGroup( $classIdentifier, $groupIdentifier );
                break;
            
            // eep createclass <Display name> <Content class group identifier>
            case self::contentclass_createclass:
                $displayName = $param1;
                // convert the display name to lowercase and solo underscores
                $classIdentifier = strtolower( trim( $displayName ) );
                $classIdentifier = preg_replace( "/[^a-z0-9]/", "_", $classIdentifier );
                $classIdentifier = preg_replace( "/_[_]+/", "_", $classIdentifier );
                if( 0 == strlen($classIdentifier) )
                {
                    throw new Exception( "Empty content class identifier" );
                }
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                if( $classId )
                {
                    throw new Exception( "This content class identifier is already used: '" . $classIdentifier . "'" );
                }
                $groupIdentifier = $param2;
                $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier );
                if( !is_object( $groupObject ) )
                {
                    throw new Exception( "Failed to locate the content class group '" . $groupIdentifier . "'" );
                }
                $groupId = $groupObject->ID;
                $this->createClass( $displayName, $classIdentifier, $groupIdentifier, $groupId );
                echo "created " . $classIdentifier . " ok\n";
                break;
            
            // eep contentclass setclassobjectidentifier <class identifier> <object naming string or pattern>
            case self::contentclass_setclassobjectidentifier:
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $contentClass->setAttribute( 'contentobject_name', $param2 );
                $contentClass->store();
                break;
            
            case self::contentclass_setiscontainer:
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $newSetting = 0;
                if( 0 != $param2 )
                {
                    $newSetting = 1;
                }
                $contentClass->setAttribute( 'is_container', $newSetting );
                $contentClass->store();
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