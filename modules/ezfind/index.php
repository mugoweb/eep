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
    const ezfind_indexnode             = "indexnode";
    const ezfind_isobjectindexed       = "isobjectindexed";
    const ezfind_eject                 = "eject";
    const ezfind_fields                = "fields";
    const ezfind_lastindexed           = "lastindexed";
    const ezfind_startsolr             = "startsolr";
    const ezfind_testquery             = "testquery";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::ezfind_indexobject
        , self::ezfind_indexnode
        , self::ezfind_isobjectindexed
        , self::ezfind_eject
        , self::ezfind_fields
        , self::ezfind_lastindexed
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

indexnode
  eep ezfind indexnode <node id>

isobjectindexed
  eep ezfind isobjectindexed <object id>

eject
  eep ezfind eject <object id>

fields
  eep ezfind fields <object id>

lastindexed 
  eep ezfind lastindexed <object id>

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
    private function indexnode( $nodeId )
    {
        $engine = new eZSolr();
        $node = eZContentObjectTreeNode::fetch( $nodeId );
        $object = eZContentObject::fetch( $node->attribute('contentobject_id') );
        if( $object )
        {
            $result = $engine->addObject( $object, false );
            $engine->commit();
        }
    }
    //--------------------------------------------------------------------------
    private function isobjectindexed( $objectId )
    {
        $engine = new eZSolr();
        $search = eZFunctionHandler::execute( 'ezfind', 'search', array( 'filter' => 'meta_id_si:' . $objectId ) );
        if( $search["SearchCount"] )
        {
            echo "yes\n";       
        }
        else
        {
            echo "no\n";
        }
    }
    //--------------------------------------------------------------------------
    private function eject( $objectId )
    {
        $engine = new eZSolr();
        $engine->removeObjectById( $objectId );        
        
    }
    //--------------------------------------------------------------------------
    private function fields( $objectId )
    {
        $search = eZFunctionHandler::execute( 'ezfind', 'search', array( 'filter' => 'meta_id_si:' . $objectId ) );
        if( count( $search["SearchResult"] ) )
        {
            foreach( $search["SearchResult"][0]->attribute('data_map') as $index => $attribute )
            {
                echo $index . "\n";
            
            }
        
        }
    }
    //--------------------------------------------------------------------------
    private function lastindexed( $objectId )
    {
        $params = array(  'baseURL' => false
                        , 'request' => '/select'
                        , 'parameters' => array( 'q' => 'meta_id_si:' . $objectId ) );
                        
        $search = eZFunctionHandler::execute( 'ezfind', 'rawSolrRequest', $params );
        if( count( $search["response"]["docs"] ) )
        {
            $datetime = strtotime( $search["response"]["docs"][( count($search["response"]["docs"]) - 1 )]["timestamp"] );
            echo date( 'Y:m:d H:i:s', $datetime ) . "\n";  
        }  
        else
        {
            if ( $this->isSolrRunning() )
            {
                echo "not-indexed\n";
            
            }
            else
            {
                echo "solr is not available\n";
            }
        }    
    }
    
    //--------------------------------------------------------------------------
    private function startsolr( $ezRootPath )
    {
        
        if ( $this->isSolrRunning() )
        {
            echo "solr is already running\n";
        
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
    private function isSolrRunning()
    {
        // look for the solr url welcome text, in case it's already started
        $ini = eZINI::instance('solr.ini');
        $url = $ini->variable("SolrBase", "SearchServerURI");
        $html = @file_get_contents( $url );
        if (strpos($html, 'Welcome to Solr!') !== false )
        {
            return true;
        
        }
        else
        {
            return false;
        }
    
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
            case self::ezfind_indexnode:
                $nodeId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $nodeId = $param1;
                }
                $this->indexnode( $nodeId );
                break;
            case self::ezfind_isobjectindexed:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $this->isobjectindexed( $objectId );
                break;
            case self::ezfind_eject:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $this->eject( $objectId );
                break;
            case self::ezfind_fields:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $this->fields( $objectId );
                break;
            case self::ezfind_lastindexed:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $this->lastindexed( $objectId );
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
