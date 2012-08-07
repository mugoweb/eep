<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/ezfind/index.php
 */

class ezfind_commands
{
    const ezfind_indexobject           = "indexobject";
    const ezfind_startsolr             = "startsolr";
    const ezfind_testquery             = "testquery";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::ezfind_indexobject
        , self::ezfind_startsolr
        , self::ezfind_testquery
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
indexobject
  eep ezfind indexobject <object id>
  
startsolr
  eep use ezroot .
  eep ezfind startsolr

testquery
  eep use ezroot .
  eep ezfind testquery
EOT;
    }
    
    //--------------------------------------------------------------------------
    private function indexobject( $objectId )
    {
        $engine = new eZSolr();
        $object = eZContentObject::fetch( $objectId );
        if( $object )
        {
            $result = $engine->addObject( $object, false );
            $engine->commit();
        }
    }
    
    //--------------------------------------------------------------------------
    private function startsolr( $ezRootPath )
    {
        // look for the xinit.d pid file, in case it's already started
        if( file_exists ( "/var/run/solr.pid" ) )
        {
            echo "'/var/run/solr.pid' already exists; solr is already running.\n";
        }
        else
        {
            $startCmd = "bash -c ";
            $startCmd .= "\"cd " . $ezRootPath . "/extension/ezfind/java/; ";
            //$startCmd .= "java -DDEBUG -Dezfind -Xms512M -Xmx512M -jar start.jar\"";
            $startCmd .= "java -Dezfind -Xms512M -Xmx512M -jar start.jar\"";
        }
        $result = shell_exec( $startCmd );
    }

    //--------------------------------------------------------------------------
    private function testQuery( $testQuery=null )
    {
        $query = "/select/?";
        $query .= "fl=score,meta_main_url_alias_s&";
        $query .= "start=0&";
        
        $query .= "q=submeta_bisac_categories-main_node_id_si:2553";
        $query .= "%20AND%20meta_path_si:1";
        $query .= "%20AND%20meta_path_si:2";
        $query .= "%20AND%20meta_path_si:93";
        $query .= "%20AND%20meta_path_si:303";
        $query .= "&";
        
        $query .= "rows=10";
        
        $result = eZFunctionHandler::execute
        (
            'ezfind'
            , 'rawSolrRequest'
            , array
            (
                'baseURL' => ""
                , 'request' => $query
            )
        );

        var_dump($result);
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
                echo "\nAvailable commands:: " . implode( ", ", $this->availableCommands ) . "\n";
                echo "\n".$this->help."\n";
                break;
            
            case self::ezfind_indexobject:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $this->indexobject( $objectId );
                break;
            
            case self::ezfind_startsolr:
                $ezRootPath = $eepCache->readFromCache( eepCache::use_key_ezroot );
                $this->startsolr( $ezRootPath );
                break;
            
            case self::ezfind_testquery:
                $this->testQuery( $param1 );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new ezfind_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>