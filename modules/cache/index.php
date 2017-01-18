<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/cache/index.php
 */

class cache_commands
{
    const cache_cacheclear = "cacheclear";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::cache_cacheclear
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
cacheclear
- executes a safe cache clear, clearing the most stuff without making the host vulnerable to
  being crushed under a high load
  eep cache cacheclear

EOT;
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
                echo "\nAvailable commands: " . implode( ", ", $this->availableCommands ) . "\n";
                echo "\n".$this->help."\n";
                break;
            
            case self::cache_cacheclear:
                //$cacheClearCommand = "php bin/php/ezcache.php --clear-id=global_ini,ini,classid,template,template-block,content,template-override,rss_cache,design_base,state_limitations";
                //echo shell_exec( $cacheClearCommand );
                $this->cacheclear();
                
                break;
        }
    }
    
    // determines the cache clear operations available, then does the most cache clearing possible without
    // doing too much - especially not clearing image aliases which tends to crush a busy server
    private function cacheclear()
    {
        $desiredIds = array
        (
            "global_ini"
            , "ini"
            , "classid"
            , "template"
            , "template-block"
            , "content"
            , "template-override"
            , "rss_cache"
            , "design_base"
            , "state_limitations"
        );
        // get the cache clear ids that are available in the current ezp version
        $getIds = "php bin/php/ezcache.php --no-colors --list-ids | awk 'NR==2 {print $0}'";
        $raw = shell_exec( $getIds );
        $availableIds = explode( ",", $raw );
        $availableIds = array_map( "trim", $availableIds );
        //print_r( $availableIds );
        // assemble all of the ids that are both available and on our desired list
        $usedIds = array();
        foreach( $desiredIds as $id )
        {
            if( in_array( $id, $availableIds ) )
            {
                $usedIds[] = $id;
            }
        }
        // clear caches
        $clearCache = "php bin/php/ezcache.php --clear-id=" . implode( ",", $usedIds );
        shell_exec( $clearCache );
    }
}

//------------------------------------------------------------------------------
$operation = new cache_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>