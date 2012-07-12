<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/use/index.php
 * note that this is slightly different than the usual module since the "keys"
 * are used instead of "commands"
 */

class use_commands
{
    const use_dump               = "dump";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , eepCache::use_key_attribute
        , eepCache::use_key_contentclass
        , eepCache::use_key_object
        , self::use_dump
        , eepCache::use_key_ezroot
        , eepCache::use_key_contentnode
        , eepCache::use_key_siteaccess
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
Save values for use with other commands. The 'commands' are the keys.
Note that "ezroot" is required when you are going to interact with eZ Publish.

use
- add a key/value to the cache
  eep use <key> <desired value>
  
dump
- print the current cached values
  eep use dump
EOT;
    }
    
    //--------------------------------------------------------------------------
    private function dumpCache()
    {
        $eepCache = eepCache::getInstance();
        $all = $eepCache->getAll();

        $results[] = array( "key",      "value" );
        foreach( $all as $key => $value )
        {
            $results[] = array( $key, $value );
        }
        // do output
        eep::printTable( $results, "eep cache" );
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
                echo "'use' available keys: " . implode( ", ", $this->availableCommands ) . "\n";
                echo "\n".$this->help."\n";
                break;
            
            case self::use_dump:
                $this->dumpCache();
                break;
            
            case eepCache::use_key_contentclass:
                $contentClass = eZContentClass::fetchByIdentifier( $param1 );
                if( !$contentClass )
                    throw new Exception( "This content class does not exist: [" . $param1 . "]" );
                
                // note that a value == "" means to clear the setting
                $eepCache->writetoCache( eepCache::use_key_contentclass, $param1 );
                break;
            
            case eepCache::use_key_contentnode:
                if( !eepValidate::validateContentNodeId( $param1 ) )
                    throw new Exception( "This is not an node id: [" .$param1. "]" );

                $eepCache->writetoCache( eepCache::use_key_contentnode, $param1 );
                break;
            
            case eepCache::use_key_object:
                if( !eepValidate::validateContentObjectId( $param1 ) )
                    throw new Exception( "This is not an object id: [" .$param1. "]" );

                $eepCache->writetoCache( eepCache::use_key_object, $param1 );
                break;

            case eepCache::use_key_attribute:
                // todo, verify that this is indeed a content class attribute
                $eepCache->writetoCache( eepCache::use_key_attribute, $param1 );
                break;
            
            case eepCache::use_key_siteaccess:
                // todo, verify that this is indeed a site access
                $eepCache->writetoCache( eepCache::use_key_siteaccess, $param1 );
                break;
            
            case eepCache::use_key_ezroot:
                if( "/" == substr( $param1, 0, 1 ) )
                {
                    // absolute path
                    $eZPublishRootPath = $param1;
                }
                else
                {
                    // relative path
                    $eZPublishRootPath = getcwd() ."/". $param1;
                }
                $eepCache->writetoCache( eepCache::use_key_ezroot, realpath( $eZPublishRootPath ) );        
                // if you require this now, you might clash with an existing
                // autoload, so just save the new path in the cache, and the
                // next run will load the new autoload
                //require $eZPublishRootPath.'/autoload.php';
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new use_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>