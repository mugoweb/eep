<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/trash/index.php
 */

class trash_commands
{
    const trash_count             = "count";
    const trash_list              = "list";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::trash_count
        , self::trash_list
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
count
  eep trash count

delete
- there is no delete function since you can use contentobject delete

list
  eep trash list

EOT;
    }

    //--------------------------------------------------------------------------
    private function trash_count()
    {
        $params = false;
        $asCount = true;
        $trashCount = eZContentObjectTrashNode::trashList( $params, $asCount );
        return $trashCount;
    }

    //--------------------------------------------------------------------------
    private function trash_list()
    {
        $params = false;
        $asCount = false;
        $trashObjects = eZContentObjectTrashNode::trashList( $params, $asCount );
        eep::displayNodeList( $trashObjects, "Garbage Nodes" );
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

            case self::trash_count:
                echo $this->trash_count() . "\n";
                break;

            case self::trash_list:
                $this->trash_list();
                break;

        }
    }
}

//------------------------------------------------------------------------------
$operation = new trash_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
