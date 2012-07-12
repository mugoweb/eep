<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/section/index.php
 */
class section_commands
{
    const section_list = "list";
    const section_allobjects = "allobjects";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::section_allobjects
        , self::section_list
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
allobjects
- list all objects in the section
- supports --limit and --offset  
  eep section allobjects <section id>

list
- list all sections
  eep section list
    
EOT;
    }

    //--------------------------------------------------------------------------
    private function section_list()
    {
        $sectionObjects = eZSection::fetchList( );
        $results = array();
        $results[] = array
        (
            "Id"
            , "NavigationPartIdentifier"
            , "Count"
            , "Name"
        );
        foreach( $sectionObjects as $section )
        {
            $count = eZSectionFunctionCollection::fetchObjectListCount( $section->ID );
            $count = $count[ "result" ];
            $results[] = array
            (
                $section->ID
                , $section->NavigationPartIdentifier
                , $count
                , $section->Name
            );
        }
        eep::printTable( $results, "all sections" );
    }
    
    //--------------------------------------------------------------------------
    //$list = eZSectionFunctionCollection::fetchObjectList( $sectionID, $offset = false, $limit = false, $sortOrder = false, $status = false )
    private function allobjects( $sectionId, $additional )
    {
        $limit = 1000;
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = 0;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }

        $title = "Objects in section " . $sectionId . " (offset=".$offset." limit=".$limit.")";
        $list = eZSectionFunctionCollection::fetchObjectList( $sectionId, $offset, $limit );
        eep::displayObjectList( $list["result"], $title );
        //var_dump($list);
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
            
            case self::section_list:
                $this->section_list();
                break;
            
            case self::section_allobjects:
                $sectionId = $param1;
                $this->allobjects( $sectionId, $additional );
                break;
        }
    } 
}

//------------------------------------------------------------------------------
$operation = new section_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>