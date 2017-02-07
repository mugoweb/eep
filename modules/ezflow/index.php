<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2014  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/ezflow/index.php
 */
class ezflow_commands
{
    const ezflow_find = "find";
    const ezflow_list = "list";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::ezflow_find
        , self::ezflow_list
    );
    var $help = ""; // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
find
- return content classes using the ezpage attribute
  eep ezflow find ezpage

  OR

- return content objects using a specific block type
  eep ezflow find blocktype <block_type>

list
- list all ezflow block types in use
  eep ezflow list blocktypes [grouped]


EOT;
    }

    //--------------------------------------------------------------------------
    private function ezflow_find_ezpage()
    {
        $db  = eZDB::instance();
        $sql = "
        SELECT
            cca.contentclass_id,
            cc.identifier
        FROM
            `ezcontentclass_attribute` cca,
            `ezcontentclass` cc
        WHERE
            cca.`data_type_string` = 'ezpage'
        AND
            cca.`contentclass_id` = cc.`id`";

        if ( $query = $db->query( $sql ) )
        {
            $results[] = array
            (
                "contentclass_id"
                , "identifier"
            );

            while( $row = $query->fetch_assoc() )
            {
                $results[] = array
                (
                    $row[ 'contentclass_id' ]
                    , $row[ 'identifier' ]
                );
            }
            eep::printTable( $results, "ezpage used in ..." );
        }
    }

    //--------------------------------------------------------------------------
    private function ezflow_find_blocktype( $block_type )
    {
        $db  = eZDB::instance();
        $sql = "
        SELECT
            b.node_id, b.name, b.zone_id, b.overflow_id,
            p.object_id, p.block_id
        FROM
            `ezm_block` b,
            `ezm_pool` p
        WHERE
            b.`block_type` = '" . mysql_real_escape_string( $block_type ) . "'
        AND
            b.`id` = p.`block_id`;";

        if ( $query = $db->query( $sql ) )
        {
            $results[] = array
            (
                "nid"
                , "oid"
                , "name"
                , "block_id"
                , "zone_id"
                , "overflow_id"
            );

            while( $row = $query->fetch_assoc() )
            {
                $results[] = array
                (
                    $row[ 'node_id' ]
                    , $row[ 'object_id' ]
                    , $row[ 'name' ]
                    , $row[ 'block_id' ]
                    , $row[ 'zone_id' ]
                    , $row[ 'overflow_id' ]
                );
            }
            eep::printTable( $results, "ezflow find blocktype [$block_type]" );
        }
    }

    //--------------------------------------------------------------------------
    private function ezflow_list_blocktypes( $output )
    {
        $validOutput = array( 'simple', 'grouped' );

        if( !in_array( $output, $validOutput ) )
        {
            $output = 'simple';
        }

        $db = eZDB::instance();

        $sql[ 'simple' ]    = "SELECT block_type, id as block_id FROM `ezm_block` ORDER BY `block_type` ASC";
        $sql[ 'grouped' ] = "
            SELECT
                block_type,
                COUNT(block_type) as count,
                (SELECT
                    GROUP_CONCAT(id)
                FROM
                    `ezm_block` t2
                WHERE
                    t1.block_type = t2.block_type
                ) as block_ids
            FROM
                `ezm_block` t1
            GROUP BY t1.block_type
            ORDER BY t1.`block_type` ASC;";

        // header field names for each output mode
        $headers[ 'simple' ]    = array
        (
            "block_type"
            , "block_id"
        );
        $headers[ 'grouped' ] = array
        (
            "block_type"
            , "count"
            , "block_ids"
        );

        if ( $query = $db->query( $sql[ $output ] ) )
        {
            // add field headers row
            $results[] = $headers[ $output ];

            while( $row = $query->fetch_array(MYSQL_NUM) )
            {
                $result = array();

                foreach( $row as $index => $value )
                {
                    $result[] = $value;
                }

                $results[] = $result;
            }

            eep::printTable( $results, "ezflow list blocktypes [{$output}]" );
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

            case self::ezflow_find:
                    if( $param1 == "ezpage" )
                    {
                        $this->ezflow_find_ezpage();
                    }

                    if( $param1 == "blocktype" && $param2 )
                    {
                        $this->ezflow_find_blocktype( $param2 );
                    }
                break;

            case self::ezflow_list:
                    if( $param1 == "blocktypes" )
                    {
                        $this->ezflow_list_blocktypes( $param2 );
                    }
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new ezflow_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
