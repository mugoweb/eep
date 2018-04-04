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
    const contentclass_info                 = "info";
    const contentclass_setfield             = "setfield";
    const contentclass_translationcreate    = "translationcreate";
    const contentclass_translationsetmain   = "translationsetmain";
    const contentclass_translationremove    = "translationremove";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentclass_appendtogroup
        , self::contentclass_createclass
        , self::contentclass_deleteclass
        , self::contentclass_fetchallinstances
        , self::contentclass_info
        , self::contentclass_listattributes
        , self::contentclass_removefromgroup
        , self::contentclass_setclassobjectidentifier
        , self::contentclass_setfield
        , self::contentclass_setiscontainer
        , self::contentclass_translationcreate
        , self::contentclass_translationsetmain
        , self::contentclass_translationremove
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
appendtogroup
   eep contentclass appendtogroup <content class identifier> <group identifier>

createclass
- create a stub content class with an automatic content class identifier and
  default string for object-naming; uses the "admin" user to create
  the class; returns the class identifier so that attributes can then be added
  and the default naming be updated
  eep createclass <Display name> <Content class group identifier>

deleteclass
- deletes all the instances of a class, and then deletes the class itself
  eep use contentclass <class identifier>
  eep contentclass deleteclass
  or
  eep contentclass deleteclass <class identifier>
  
fetchallinstances
  - note that this supports limit and offset parameters
  eep use contentclass <class identifier>
  eep contentclass fetchallinstances
  - or -
  eep contentclass fetchallinstances <content class identifier>

info
- dumps the internal fields that ez manages for the content class, like 'url pattern' and etc.
  eep contentclass info <class identifier>
  
listattributes
  eep use contentclass <class identifier>
  eep contentclass listattributes

removefromgroup
   eep contentclass removefromgroup <content class identifier> <group identifier>
  
setclassobjectidentifier
- set the string used to name instances of the class, uses the same syntax as in
  the admin ui
  eep contentclass setclassobjectidentifier <class identifier> <object naming string or pattern>

setfield  
- set any of the internal fields that ez manages, see 'info' for the list
  eep contentclass setfield <content class identifier> <field name> <new value>
  
setiscontainer
- set or unset the 'is container' flag on the class
  eep contentclass setiscontainer <class identifier> <0|1>

translationcreate
- add a new translation for the content class, optionally copy the translation from an existing one
  note that 'locale's are, eg., eng-GB or eng-US
  eep contentclass translationcreate <class identifier> <new locale> [<existing locale>]

translationsetmain
- set the main translation, eg. in preparation to removing eng-GB as a supported translation
  eep contentclass translationsetmain <class identifier> <locale>
  
translationremove
- remove a translation from the content class
  eep contentclass translationremove <class identifier> <locale>
  
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

        // need to operate in a privileged account
        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        $adminUserObject->loginCurrent();

        while( $moreToDelete )
        {
            $children = eZContentObject::fetchSameClassList( $classId, false, 0, $chunkSize ); // false to indicate, 'fetch not as objects'
            foreach( $children as $child )
            {
                eZContentObjectOperations::remove( $child[ "id" ], false ); // false to indicate, 'just purge it'
                $totalDeleted += 1;
                echo "Percent complete: " . sprintf( "% 3.3f", ($totalDeleted / $totalObjectCount)*100.0  ) . "%\r";
                unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
                unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
                unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
                $moreToDelete = 0 < eZContentObject::fetchSameClassListCount( $classId );
            }
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
        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        if( null === $adminUserObject )
        {
            throw new Exception( "eepSetting::PrivilegedAccountId value of " . eepSetting::PrivilegedAccountId . " is invalid. Exiting." );
            exit( 1 );
        }
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

            case self::contentclass_info:
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $def = eZContentClass::definition();
                $results[] = array( "Field Name", "Field Value", "Value Type" );
                foreach( $def[ "fields" ] as $name => $fieldSettings )
                {
                    $results[] = array( $name, $contentClass->attribute( $name ), $fieldSettings[ "datatype" ] );
                }
                eep::printTable( $results, "Class fields" );
                break;
            
            case self::contentclass_setfield:
                // test the content class identifier, and fetch the content class object
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                // validate the field name
                $fieldName = $param2;
                $def = eZContentClass::definition();
                if( !isset( $def[ "fields" ][ $fieldName ] ) )
                {
                    throw new Exception( "This is not a valid field name. [" . $fieldName . "]" );
                }
                // and set the value into the content class
                $contentClass->setAttribute( $fieldName, $param3 );
                $contentClass->store();
                break;
  
            case self::contentclass_translationcreate:
                // eep contentclass translationcreate <class identifier> <new locale> [<existing locale>]
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $newLocale = $param2;
                $newLocalId = false; // just using this for the validation step
                // validate or create the new locale
                $languageList = eZContentLanguage::fetchList( true /*force reload*/ );
                foreach( $languageList as $languageId => $eZContentLanguage )
                {
                    if( $newLocale == $eZContentLanguage->Locale )
                    {
                        $newLocalId = $languageId;
                    }
                }
                if( false === $newLocalId )
                {
                    // so, we did not find the requested language/locale, so we have to add it
                    $newLocalId = eZContentLanguage::addLanguage( $newLocale );
                }
                if( !($newLocalId > 0) )
                {
                    throw new Exception( "Failed to locate or create locale: [" . $newLocale . "]" );
                }
                // todo, should validate the sourceLocale somehow ... make sure that the content class does have that translation?
                $sourceLocale = $param3;
                // init the translatable values in the content class
                $attributes = $contentClass->fetchAttributes();
                foreach( array_keys( $attributes ) as $key )
                {
                    if( $sourceLocale )
                    {
                        $name         = $attributes[$key]->name( $sourceLocale );
                        $description  = $attributes[$key]->description( $sourceLocale );
                        $i18nDataText = $attributes[$key]->dataTextI18n( $sourceLocale );
                    }
                    else
                    {
                        $name         = "";
                        $description  = "";
                        $i18nDataText = "";
                    }
                    $attributes[$key]->setName( $name, $newLocale );
                    $attributes[$key]->setDescription( $description, $newLocale );
                    $attributes[$key]->setDataTextI18n( $i18nDataText, $newLocale );
                }
                $contentClass->store( $attributes );
                if( $sourceLocale )
                {
                    $name = $contentClass->name( $sourceLocale );
                    $description = $contentClass->description( $sourceLocale );
                }
                else
                {
                    $name = "";
                    $description = "";
                }
                $contentClass->setName( $name, $newLocale );
                $contentClass->setDescription( $description, $newLocale );
                $contentClass->store();
                echo "Added translation [" . $locale . "] to " . $classIdentifier . "\n";
                break;
            
            case self::contentclass_translationsetmain:
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $locale = $param2;
                $languageList = eZContentLanguage::fetchList( true /*force reload*/ );
                // get the id of the desired translation and set it to be the main
                $success = false;
                foreach( $languageList as $languageId => $eZContentLanguage )
                {
                    if( $locale == $eZContentLanguage->Locale )
                    {
                        $contentClass->setAttribute( 'initial_language_id', $languageId );
                        $contentClass->setAlwaysAvailableLanguageID( $languageId );
                        $contentClass->store();
                        $success = true;
                    }
                }
                if( $success )
                {
                    echo "Set main " . $locale . " with id " . $languageId . "\n";
                }
                else
                {
                    echo "Failed to locate locale [" . $locale . "] did not set main.\n";
                }
                break;
            
            case self::contentclass_translationremove:
                $classIdentifier = $param1;
                $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
                $contentClass = eZContentClass::fetch( $classId );
                if( !is_object( $contentClass ) )
                {
                    throw new Exception( "Failed to instantiate content class. [" . $classIdentifier . "]" );
                }
                $locale = $param2;
                $languageList = eZContentLanguage::fetchList( true /*force reload*/ );
                $success = false;
                foreach( $languageList as $languageId => $eZContentLanguage )
                {
                    if( $locale == $eZContentLanguage->Locale )
                    {
                        $contentClass->removeTranslation( $languageId );
                        $contentClass->store();
                        $success = true;
                    }
                }
                if( $success )
                {
                    echo "Removed translation " . $locale . " with id " . $languageId . "\n";
                }
                else
                {
                    echo "Failed to locate locale [" . $locale . "] did not remove translation.\n";
                }
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
