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
    const user_editlog = "editlog";
    const user_visit = "visit";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::user_visit
        , self::user_editlog
    );
    var $help = ""; // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
editlog
- dump list of users who have edited content in the last N months (defaults to 3) and who have edited more than 1 piece of content.
eep user editlog

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
            throw new Exception( "user_id parameter missing" );   
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
                "current_visit_timestamp"
                , "failed_login_attempts"
                , "last_visit_timestamp"
                , "login_count"
                , "user_id"
            );

            while( $row = $query->fetch_assoc() )
            {
                $results[] = array
                ( 
                    $row[ "current_visit_timestamp" ]
                    , $row[ "failed_login_attempts" ]
                    , $row[ "last_visit_timestamp" ]
                    , $row[ "login_count" ]
                    , $row[ "user_id" ]
                );
            }
            if ( count( $results ) > 1 )
            {
                eep::printTable( $results, "user visit [{$userId}]" );
            }
        }
    }
    
    //--------------------------------------------------------------------------
    private function user_editlog( $durationOverride )
    {
        $months = 3;
        if( 0 < (integer )$durationOverride )
        {
            $months = (integer )$durationOverride;
        }
        $duration = 30 * 24 * 60 * 60 * $months;
        
        $db  = eZDB::instance();
        $sql = "SELECT
                    COUNT( DISTINCT ezcontentobject_version.creator_id, ezcontentobject_version.contentobject_id ) as editor_count,
                    ezuser.email,
                    ezcontentobject_version.creator_id
                FROM ezcontentobject_version, ezuser
                WHERE ezuser.contentobject_id = ezcontentobject_version.creator_id
                    AND ezcontentobject_version.modified > ( UNIX_TIMESTAMP() - " . $duration . " )
                GROUP BY ezcontentobject_version.creator_id
                HAVING editor_count > 1
                ORDER BY editor_count DESC";

        if ( $query = $db->query( $sql ) )
        {
            $results[] = array
            (
                "editor_count"
                , "email"
                , "creator_id"
            );

            while( $row = $query->fetch_assoc() )
            {
                $results[] = array
                ( 
                    $row[ "editor_count" ]
                    , $row[ "email" ]
                    , $row[ "creator_id" ] 
                );
            }
            eep::printTable( $results, "Numbers of objects edited in last " . $months . " months" );
        }
        else
        {
            throw new Exception( "SQL failed to run -- sorry.\n" );   
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
            
            case self::user_editlog:
                $this->user_editlog( $param1 );
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