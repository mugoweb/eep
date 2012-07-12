<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/

class help_commands
{
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
- provides some help
  eep help
  or
  eep <module> help
  or
  eep help <module>
EOT;
    }

    //--------------------------------------------------------------------------
    public function run( $argv, $additional )
    {
        $command = @$argv[2];
        $param1 = @$argv[3];
        $param2 = @$argv[4];

        $eepCache = eepCache::getInstance();

        $availableModules = $eepCache->readFromCache( eepCache::misc_key_availablemodules );
        if( !in_array( $command, $availableModules ) )
        {
            throw new Exception( "Command '" . $command . "' not recognized." );
        }

        switch( $command )
        {
            case "help":
                sort( $availableModules );
                echo "\nAvailable modules: " . implode( $availableModules, ", " ) . "\n";
                echo "\n". $this->help . "\n";
                $aliases = eep::getListOfAliases();
                $table = array();
                $table[] = array( "Alias", "Command or Module" );
                foreach( $aliases as $alias => $full )
                {
                    $table[] = array( $alias, $full );
                }
                eep::printTable( $table, "Available shortcuts" );
                break;
            
            default:
                // this is an infamous hack; redirect the request to a different
                // module, and the help function there
                global $argv;
                global $argc;
                
                $argv[1] = $command; // the module, not actually used
                $argv[2] = "help";   // the command, which is help
                
                global $eepPath;
                require_once( $eepPath . "/modules/" . $command . "/index.php" );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new help_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>