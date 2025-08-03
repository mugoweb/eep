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
    const role_deleterole = "deleterole";
    const role_assignrole = "assignrole";
    const role_addmodulepolicy = "addmodulepolicy";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::role_createrole
        , self::role_listroles
        , self::role_deleterole
        , self::role_assignrole
        , self::role_addmodulepolicy
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

deleterole
- remove role based on the role id
eep role deleterole <role id>

assignrole
- assign role to either user group or user object
eep role assignrole <role id> <user object id>

addmodulepolicy
- create new policy and add to role
eep role addmodulepolicy <role id> <module name> <function name or *>

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
	private function role_deleterole( $roleid )
    {
		$role = eZRole::fetch( $roleid );
		if( $role )
		{
			eZRole::removeRole( $roleid );
			echo "Removed role id: " . $roleid . " ok\n";
		}
		else
		{
			throw new Exception( "Failed to locate role: $roleid" );
		}
	}

    //--------------------------------------------------------------------------
	private function role_assignrole( $roleid, $objectid )
    {
		$role = eZRole::fetch( $roleid );
		if( !$role ) throw new Exception( "Failed to locate role: $roleid" );

        $obj = eZContentObject::fetch( $objectid );
		if( !$obj ) throw new Exception( "Failed to locate user or user-group object with oid: " . $objectid . "\n" );

        $role->assignToUser( $objectid );
    
        echo "ok\n";
	}

    //--------------------------------------------------------------------------
	private function role_addmodulepolicy( $roleid, $modulename, $functionname )
	{
		$role = eZRole::fetch( $roleid );
		if( !$role ) throw new Exception( "Failed to locate role: $roleid" );
			
		$role->appendPolicy( $modulename, $functionname );

/*

		// how to validate the module name?

		$policyData = array
		(
			"module_name"	  => "b2b"
			, "function_name" => "*"
		);

        $newPolicy = eZPolicy::createNew( $roleID , $policyData );

print_r( $newPolicy );
 */


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

            case self::role_deleterole:
                $this->role_deleterole( $param1 );
                break;

            case self::role_assignrole:
                $this->role_assignrole( $param1, $param2 );
                break;

            case self::role_addmodulepolicy:
                $this->role_addmodulepolicy( $param1, $param2, $param3 );
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
