<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/knowledgebase/index.php
 */

class knowledgebase_commands
{
    const knowledgebase_ezdebug         = "ezdebug";
    const knowledgebase_vhost           = "vhost";
    const knowledgebase_sqltofixenglish = "sqltofixenglish";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::knowledgebase_ezdebug
        , self::knowledgebase_vhost
        , self::knowledgebase_sqltofixenglish
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
ezdebug
- outputs useful INI settings to set up for debugging
  eep knowledgebase ezdebug

vhost
- outputs a useful apache virtual host file
  eep knowledgebase vhost
  
sqltofixenglish
- outputs some sql that will convert all the UK translations of content classes
to US
  eep knowledgebase sqltofixenglish

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
            
            case self::knowledgebase_ezdebug:
                require_once( "knowledgebase_ezdebug.php" );
                echo $knowledgeBaseString;
                break;
            
            case self::knowledgebase_vhost:
                if( !$eepCache->cacheKeyIsSet( eepCache::use_key_ezroot ) )
                    throw new Exception( "This requires 'eep use ezroot ...'" );
                $ezRoot = $eepCache->readFromCache( eepCache::use_key_ezroot );
                
                require_once( "knowledgebase_vhost.php" );
                
                $knowledgeBaseString = str_replace( "<<<ezroot>>>", $ezRoot, $knowledgeBaseString );
                echo $knowledgeBaseString;
                break;

            case self::knowledgebase_sqltofixenglish:
                require_once( "knowledgebase_resetlanguagesql.php" );
                echo $knowledgeBaseString;
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new knowledgebase_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>