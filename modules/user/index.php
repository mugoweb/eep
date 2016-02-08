<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2014  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/user/index.php
 */
class user_commands
{
    const user_visit = "visit";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::user_visit
    );
    var $help = ""; // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
visit
- return user visit information e.g. last login, login count

eep user visit <user_id>
    
EOT;
    }

    //--------------------------------------------------------------------------
    private function user_visit( $userId )
    {
        $userId = (integer) $userId;

        if( !$userId )
        {
            throw new Exception( 'user_id parameter missing' );   
        }
        if( !eepValidate::validateContentObjectId( $userId ) )
        {
            throw new Exception( "This is not an object id: [" .$userId. "]" );
        }

        $db  = eZDB::instance();
        $sql = "
        SELECT 
            *
        FROM 
            `ezuservisit` uv
        WHERE
            uv.`user_id` = {$userId};";

        if ( $query = $db->query( $sql ) )
        {
            $results[] = array
            (
                'current_visit_timestamp'
                , 'failed_login_attempts'
                , 'last_visit_timestamp'
                , 'login_count'
                , 'user_id'
            );

            while( $row = $query->fetch_assoc() )
            {
                $results[] = array
                ( 
                    $row[ 'current_visit_timestamp' ]
                    , $row[ 'failed_login_attempts' ]
                    , $row[ 'last_visit_timestamp' ]
                    , $row[ 'login_count' ]
                    , $row[ 'user_id' ]
                );
            }
            if ( count( $results ) > 1 )
            {
                eep::printTable( $results, "user visit [{$userId}]" );
            }
        }
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

            case self::user_visit:
                    $this->user_visit( $param1 );
                break;
        }
    } 
}

//------------------------------------------------------------------------------
$operation = new user_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>