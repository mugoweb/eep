<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/crondaemon/index.php
 *
 * todo: add function to list all the used 'task types', prob have to query the preferences table
 */

class crondaemon_commands
{
    const crondaemon_addtask  = "addtask";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::crondaemon_addtask
    );
    var $help = "";                     // used to dump the help string
        
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
addtask
  eep crondaemon addtask <task type> <task> <priority = 500>
  - priority is 1 to 999, 999 is the highest
EOT;
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
            
            case self::crondaemon_addtask:
                
                if( (0 == strlen($param1)) || (0 == strlen($param2)) )
                {
                    throw new Exception( "This requires at least two parameters." );
                }
                eep::addTask( $param1, $param2, $param3 );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new crondaemon_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}

$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>