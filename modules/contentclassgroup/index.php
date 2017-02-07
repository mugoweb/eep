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

class contentclassgroup_commands
{
    const contentclassgroup_creategroup          = "creategroup";
    const contentclassgroup_deletegroup          = "deletegroup";
    const contentclassgroup_renamegroup          = "renamegroup";
    const contentclassgroup_fetchall             = "fetchall";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentclassgroup_creategroup
        , self::contentclassgroup_deletegroup
        , self::contentclassgroup_renamegroup
        , self::contentclassgroup_fetchall
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
creategroup
- creates a new Content Class Group
  eep use ezroot <path>
  eep contentclassgroup creategroup <group identifier>

deletegroup
- deletes the specified Content Class Group
  eep use ezroot <path>
  eep contentclassgroup deletegroup <group identifier>

renamegroup
- renames a Content Class Group
  eep use ezroot <path>
  eep contentclassgroup renamegroup <group identifier from> <group identifier to>

fetchall
- displays all Content Class Groups
  eep use ezroot <path>
  eep contentclassgroup fetchall
EOT;
    }

    //--------------------------------------------------------------------------
    // creates a content class group with the given identifier
    private function creategroup( $groupIdentifier )
    {
        $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier, true );
        if( $groupObject != null )
        {
            echo "Group identifier already exists\n";
            return;
        }
        $classgroup = eZContentClassGroup::create( false );
        $classgroup->setAttribute( "name", ezpI18n::tr( 'kernel/class/groupedit', $groupIdentifier ) );
        $classgroup->store();
        echo "Successfully created content class group $groupIdentifier\n";
    }

    //--------------------------------------------------------------------------
    // creates a content class group with the given identifier
    private function deletegroup( $groupIdentifier )
    {
        $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier, true );
        if( $groupObject == null )
        {
            echo "Invalid group identifier\n";
            return;
        }
        eZContentClassGroup::removeSelected( $groupObject->ID );
        echo "Successfully deleted content class group $groupIdentifier\n";
    }

    //--------------------------------------------------------------------------
    // renames a content class group with the given groupIdentifier to the
    // string specified
    private function renamegroup( $groupIdentifier, $newGroupIdentifier )
    {
        $groupObject = eZContentClassGroup::fetchByName( $groupIdentifier, true );
        if( $groupObject == null )
        {
            echo "Invalid group identifier\n";
            return;
        }

        $date_time = time();
        $groupObject->setAttribute( "modified", $date_time );
        $groupObject->setAttribute( "modifier_id", false );
        $groupObject->setAttribute( "name", $newGroupIdentifier );
        $groupObject->store();

        eZContentClassClassGroup::update( null, $groupObject->ID, $newGroupIdentifier );

        echo "Successfully renamed group $groupIdentifier to $newGroupIdentifier\n";
    }

    //--------------------------------------------------------------------------
    private function fetchall( )
    {
        $limit = false;
        $results = array();
        $results[] = array
        (
            "ID"
            , "Name"
        );
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = false;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }

        $allInstances = eZContentClassGroup::fetchList( false, false );
        $title = "Viewing all groups";


        foreach( $allInstances as $group )
        {
            $results[] = array(
                $group["id"],
                $group["name"]
            );
        }

        eep::printTable( $results, $title );
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

            case self::contentclassgroup_creategroup:
                if( $param1 )
                {
                    $groupIdentifier = $param1;
                    $this->creategroup( $groupIdentifier );
                }
                else
                {
                    echo "Please specify the identifier for the content class group you are creating\n";
                }
                break;

            case self::contentclassgroup_deletegroup:
                if( $param1 )
                {
                    $groupIdentifier = $param1;
                    $this->deletegroup( $groupIdentifier );
                }
                else
                {
                    echo "Please specify the identifier for the content class group you are creating\n";
                }
                break;

            case self::contentclassgroup_renamegroup:
                if( $param1 && $param2 )
                {
                    $groupIdentifier = $param1;
                    $newGroupIdentifier    = $param2;
                    $this->renamegroup( $groupIdentifier, $newGroupIdentifier );
                }
                else
                {
                    echo "Please specify the identifiers of the group before and after renaming\n";
                }
                break;

            case self::contentclassgroup_fetchall:
                $this->fetchall( );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new contentclassgroup_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
