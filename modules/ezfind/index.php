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
    const ezfind_advanced              = "advanced";
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
        , self::ezfind_advanced
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
advanced
  eep ezfind advanced <statement> <fields to return> <filter> [--offset=## --limit=## --show-complex=1 --output=xml|csv|json]

  - Using --output requires the relevant queryResponseWriter
    to be enabled in ezfind/java/solr/conf/solrconfig.xml

  Example: eep ezfind advanced 'Water*' 'meta_node_id_si,attr_title_s' 'meta_class_identifier_ms:article' --show-complex=1 --output=json

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
    private function advanced( $args, $additional )
    {
        $parameters = array();
        $limit = 10; // Solr default; TODO: see if we can get this from the Solr config instead
        // set the query string
        if( isset( $args[3] ) )
        {
            $parameters['q'] = $args[3];
        }
        else
        {
            echo "Query is required\n";
            return;
        }
        // fields to list
        if( isset( $args[4] ) )
        {
            $parameters['fl'] = $args[4];
        }
        // fields to query
        if( isset( $args[5] ) )
        {
            $parameters['fq'] = $args[5];
        }
        // offset and limit
        if( isset( $additional['offset'] ) )
        {
            $parameters['start'] = $additional['offset'];
        }
        if( isset( $additional['limit'] ) )
        {
            $parameters['rows'] = $additional['limit'];
            $limit = $additional['limit'];
        }
        // output format
        if( isset( $additional['output'] ) && $additional['output'] == 'xml' )
        {
            $parameters['wt'] = 'xml';
        }

        $showComplex = false;
        if( isset( $additional['show-complex'] ) )
        {
            $showComplex = true;
        }

        $responseWriter = 'php'; // default eZFind Solr QueryResponseWriter
        if( isset( $additional['output'] ) )
        {
            // xml, json, csv etc;
            // check /ezfind/java/solr/conf/solrconfig.xml for other valid/enabled queryResponseWriter
            $responseWriter = $additional['output'];
        }
        // run request directly to avoid hardcoded 'wt' parameter issue when using eZFunctionHandler
        $solr = new eZSolrBase( false );
        $search = $solr->rawSolrRequest( '/select', $parameters, $responseWriter );
        if( isset( $search['response']['numFound'] ) && $search['response']['numFound'] > 0 && !isset( $additional['output'] ) )
        {
            $results = array();
            $header = array();
            $numFound = $search['response']['numFound'];
            $start = $search['response']['start'];
            if( count($search['response']['docs']) > 0 )
            {
                foreach( $search['response']['docs'][0] as $index => $doc )
                {
                    $header[] = $index;
                }
                
                $results[] = $header;
                $fieldListCount = count( explode( ',', $parameters['fl'] ) );
                foreach( $search['response']['docs'] as $doc )
                {
                    $result = array();
                    $resultCount = count( $doc );
                    foreach( $doc as $attribute )
                    {
                        if( is_array( $attribute ) )
                        {
                            if( $showComplex )
                            {
                                $result[] = implode( '¦', $attribute );
                            }
                            else
                            {
                                $result[] = 'array()';
                            }
                        }
                        else if( is_object( $attribute ) )
                        {
                            
                            if( $showComplex )
                            {
                                $result[] = implode( '¦', (array) $attribute );
                            }
                            else
                            {
                                $result[] = 'object()';
                            }
                        }
                        else
                        {
                            $result[] = $attribute;
                        }
                    }

                    // Not all docs return all fields queried for.
                    // Fix the results table display by adding placeholders for those cases
                    if( $resultCount !== $fieldListCount )
                    {
                        $diff = $fieldListCount - $resultCount;
                        for( $i = 0; $i < $diff; $i++ )
                        {
                            $result[] = 'no value';
                        }
                    }

                    $results[] = $result;
                }

                eep::printTable( $results, "List ezfind results [{$limit}/{$start}/{$numFound}]" );
            }
        }
        else if( $search && isset( $additional['output'] ) && in_array( $additional['output'], array( 'xml', 'csv' ) ) )
        {
            echo $search[0];
        }
        else if ( $search && isset( $additional['output'] ) && $additional['output'] == 'json' )
        {
            // TODO: eZ is json_decode'ing requests made with wt=json before passing back the result set
            // figure out a better way around that
            //
            // In the meantime
            echo json_encode( $search ) . "\n";
        }
        else
        {
            echo "No results\n";
        }
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
        $object = eZContentObject::fetch( $objectId );
        if( $object )
        {
            $engine = new eZSolr();
            $engine->removeObjectById( $objectId );
        }
        else
        {
            echo "invalid object id\n";
        }        
        
    }
    //--------------------------------------------------------------------------
    private function fields( $objectId )
    {
        $parameters = array();       
        $parameters['q'] = 'meta_id_si:' . $objectId ;
        $query  = array
        (
            'baseURL'       => false
            , 'request'     => '/select'
            , 'parameters'  => $parameters
        );
        $search = eZFunctionHandler::execute( 'ezfind', 'rawSolrRequest', $query );
        if( $search['response']['numFound'] > 0 )
        {
            $results = array();
            $header = array( 'Field', 'Has data', 'Multi valued' );
            $results[] = $header;
            foreach( $search['response']['docs'][0] as $index => $doc )
            {
                $hasData = 'no';
                $multiValued = 'no';
                if( is_array($doc) && count($doc) )
                {
                   $hasData = 'yes';
                   $multiValued = 'yes';
                }
                elseif( $doc !== '' )
                {
                    $hasData = 'yes';
                }
                $results[] = array( $index, $hasData, $multiValued );
            }
            eep::printTable( $results, "List ezfind fields [$objectId]" );
        }
        else
        {
            echo "No results\n";        
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

            echo "not-indexed\n";           

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
        
        if ( !$this->isSolrRunning() 
             && $command != self::ezfind_startsolr
             && $command != "help" )
        {
            echo "solr is not available\n";
        
        }
        else
        {

            $eepCache = eepCache::getInstance();

            switch( $command )
            {
                case "help":
                    echo "\nAvailable commands:: " . implode( ", ", $this->availableCommands ) . "\n";
                    echo "\n".$this->help."\n";
                    break;
                
                case self::ezfind_advanced:                    
                    $this->advanced( $argv, $additional );
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
