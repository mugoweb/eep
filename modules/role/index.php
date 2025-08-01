<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2014  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/role/index.php
 */
class role_commands
{
    const role_createrole = "createrole";
    const role_listroles = "listroles";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::role_createrole
        , self::role_listroles
    );
    var $help = ""; // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
createrole
- create new role with no policies
eep role createrole <new role name>

listrole
- list all roles, including temporary ones
eep role listroles

EOT;
    }

    //--------------------------------------------------------------------------
	private function role_createrole( $newRoleName )
    {
        $db = eZDB::instance();
        $db->begin();

        $newRole = eZRole::createNew();
        $newRole->setAttribute( 'name', $newRoleName );
        $newRole->store();

        $db->commit();

		echo "Created new role with id: " . $newRole->ID . " and name: " . $newRole->Name . "\n";
    }

    //--------------------------------------------------------------------------
	private function role_listroles()
    {
		$roles = eZRole::fetchList();
		//print_r( $roles );

        $results[] = array
		(
			"rolename"
			, "roleid"
			, "numpolicies"
		);
		foreach( $roles as $n => $roledata )
		{
			$roleObj = eZRole::fetch( $roledata->ID );


//print_r( $roleObj->policyList() );


			$results[] = array
			(
				$roledata->Name
				, $roledata->ID
				, count( $roleObj->policyList() )
			);
		}
		eep::printTable( $results, "User roles" );
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

            case self::role_createrole:
                $this->role_createrole( $param1 );
                break;

            case self::role_listroles:
                $this->role_listroles();
                break;

        }
    } 
}

//------------------------------------------------------------------------------
$operation = new role_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>
