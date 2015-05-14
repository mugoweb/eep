<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
class eep
{
    const NOATTRIBUTE = 0;
    const REVRELATED = true;
    const FORRELATED = false;

    static function printTable( $table, $description="" )
    {
        // calc column widths
        $widths = array();
        foreach( $table as $r => $row )
        {
            foreach( $row as $column => $part )
            {
                // trim the value - suppress the warning if looking at an object
                $table[ $r ][ $column ] = @trim( $part );
                // and calc the widths - continue to suppress warnings
                if( !isset( $widths[$column] ) )
                {
                    $widths[ $column ] = @strlen( $part );
                }
                elseif( $widths[ $column ] < @strlen( $part ) )
                {
                    $widths[ $column ] = @strlen( $part );
                }
            }
        }

        // build the horizontal line
        $horParts = array();
        foreach( $widths as $width )
        {
            $horParts[] = str_repeat( "-", $width );
        }
        $horLine = " +-" . implode( "-+-", $horParts ) . "-+\n";

        // do the output
        $doneTitles = false;
        echo "\n" . $horLine;
        $pipe = " I "; // only write pipes on data lines
        if( $description )
        {
            echo $pipe.str_pad( $description, strlen($horLine)-strlen($doneTitles)-6, " ", STR_PAD_RIGHT )." | \n";
            echo $horLine;
            $pipe = " | ";
        }
        $pipe = " I "; // only write pipes on data lines
        foreach( $table as $row )
        {
            $align = ""; // two column tables have the second column aligned left
            foreach( $row as $column => $part )
            {
                if( 2==count($row) && 1==$column ) $align = "-";
                echo $pipe. sprintf( "% ".$align.$widths[$column]."s", $part);
                $pipe = " | ";
            }
            echo " | \n";
            if( !$doneTitles )
            {
                echo $horLine;
                $doneTitles = true;
            }
        }
        echo $horLine;
    }

    //--------------------------------------------------------------------------
    // given an XML string, extract parameters
    // depends on attributes:
    //   required
    //   validation
    // used to validate XML inputs to complex operations
    //static function extractParameters( $xml ) no longer used
    /*
    {
        $params = array();

        libxml_use_internal_errors( true );
        $simpleXML = simplexml_load_string( $xml );

        //var_dump( $simpleXML );

        if( false === $simpleXML )
        {
            $errMsg = "Failed to parse the XML\n";
            foreach( libxml_get_errors() as $error )
            {
                $errMsg .= "\t" . $error->message;
                throw new Exception( $errMsg );
            }
        }

        foreach( $simpleXML as $index => $element )
        {
            $value = trim( (string )$element );
            if( "nullable" == $element->attributes()->required )
            {
                if( "null" == $value )
                    $value = null;
            }
            elseif( "required" == $element->attributes()->required )
            {
                if( "" == $value )
                    throw new Exception( "'" . $index . "' is required in input XML" );
            }
            $validationType = $element->attributes()->validation;
            if( $validationType )
            {
                $errorMessage = eep::attemptValidationOnParameter( $value, $validationType );
                if( $errorMessage )
                    throw new Exception( "There is a problem with: '" . $index . "': " . $errorMessage );
            }

            if( $value == "additional_for_specific_datatype" )
            {
                // currently ignoring this
                continue;
            }
            $params[ $index ] = $value;
        }
        return $params;
    }
    */

    //--------------------------------------------------------------------------
    // helper for extractParameters()
    // simply makes a check on the provided value against a rule associated with
    // the validationtype and returns a message if there is a problem
    //function attemptValidationOnParameter( $value, $validationType ) no longer used
    /*
    {
        switch( $validationType )
        {
            default:
                echo "There is currently no code for validating: '" . $validationType . "'\n";
                break;

            case "none":
                break;

            case "bit":
                if( !in_array( $value, array( "0", "1" ) ) )
                    return "Value should be '0' or '1'";
                break;

            case "identifier":
                $cleaned = preg_replace( "/[^a-z0-9_]/", "", $value );
                if( $cleaned != $value )
                    return "Invalid identifier. Instead of '" .$value. "' use '" .$cleaned. "'";
                break;

            case "language":
                if( !in_array( $value, AttributeFunctions::$someLanguages ) )
                    return "'" .$value. "' is not a valid language";
                break;
        }
        // signal that there are no problems
        return "";
    }
    */

    //--------------------------------------------------------------------------
    static function getListOfAliases()
    {
        $aliases = array
        (
        // module shortcuts
            "at"    => "attribute"
            , "cc"  => "contentclass"
            , "co"  => "contentobject"
            , "cn"  => "contentnode"
            , "kb"  => "knowledgebase"
            , "ccg" => "contentclassgroup"
        // method shortcuts
            , "ats"  => "attributes"
            , "ccs"  => "contentclasses"
            , "cos"  => "contentobjects"
            , "cns"  => "contentnodes"
            , "coid"  => "contentobjectid"
        );
        return $aliases;
    }

    //--------------------------------------------------------------------------
    static function expandAliases( $alias )
    {
        $aliases = eep::getListOfAliases();
        if( isset( $aliases[$alias] ))
        {
            return $aliases[$alias];
        }
        else
        {
            return $alias;
        }
    }

    //--------------------------------------------------------------------------
    /*
     * Fetch the number of (reverse) related objects
     *
     * @param int $attributeID
     *        This parameter only makes sense if $params[AllRelations] is unset,
     *        set to false, or matches eZContentObject::RELATION_ATTRIBUTE
     *        Possible values:
     *        - 0 or false:
     *          Count relations made with any attribute
     *        - >0
     *          Count relations made with attribute $attributeID
     * @param int|false $reverseRelatedObjects
     *        Wether to count related objects (false) or reverse related
     *        objects (false)
     * @param array|false $params
     *        Various params, as an associative array.
     *        Possible values:
     *        - AllRelations (bool|int)
     *          true: count ALL relations, object and attribute level
     *          false: only count object level relations
     *          other: bit mask of eZContentObject::RELATION_* constants
     *        - IgnoreVisibility (bool)
     *          If true, 'hidden' status will be ignored
     *
     * @return int The number of (reverse) related objects for the object
     */
    // note that this is ripped from ... /kernel/classes/ezcontentobject.php ... relatedObjectCount()
    static function fastRelatedObjectCount( $objectId, $objectVersion, $attributeID=0, $reverseRelatedObjects=false, $params=false )
    {
        $db = eZDB::instance();
        $showInvisibleNodesCond = '';

        // process params (only IgnoreVisibility currently supported):
        if ( is_array( $params ) )
        {
            if ( isset( $params['IgnoreVisibility'] ) )
            {
                $showInvisibleNodesCond = eZContentObject::createFilterByVisibilitySQLString( $params['IgnoreVisibility'] );
            }
        }

        $relationTypeMasking = '';
        $relationTypeMask = isset( $params['AllRelations'] ) ? $params['AllRelations'] : ( $attributeID === false );
        if ( $attributeID && ( $relationTypeMask === false || $relationTypeMask === eZContentObject::RELATION_ATTRIBUTE ) )
        {
            $attributeID =(int) $attributeID;
            $relationTypeMasking .= " AND contentclassattribute_id=$attributeID ";
            $relationTypeMask = eZContentObject::RELATION_ATTRIBUTE;
        }
        elseif ( is_bool( $relationTypeMask ) )
        {
            $relationTypeMask = eZContentObject::relationTypeMask( $relationTypeMask );
        }

        if ( $db->databaseName() == 'oracle' )
        {
            $relationTypeMasking .= " AND bitand( relation_type, $relationTypeMask ) <> 0 ";
        }
        else
        {
            $relationTypeMasking .= " AND ( relation_type & $relationTypeMask ) <> 0 ";
        }

        if ( $reverseRelatedObjects )
        {
            if ( is_array( $objectId ) )
            {
                if ( count( $objectId ) > 0 )
                {
                    $objectIDSQL = ' AND ' . $db->generateSQLINStatement( $objectId, 'ezcontentobject_link.to_contentobject_id', false, false, 'int' ) . ' AND
                                    ezcontentobject_link.from_contentobject_version=ezcontentobject.current_version';
                }
                else
                {
                    $objectIDSQL = '';
                }
            }
            else
            {
                $objectId = (int) $objectId;
                $objectIDSQL = ' AND ezcontentobject_link.to_contentobject_id = ' .  $objectId . ' AND
                                ezcontentobject_link.from_contentobject_version=ezcontentobject.current_version';
            }
            $select = " count( DISTINCT ezcontentobject.id ) AS count";
        }
        else
        {
            $select = " count( ezcontentobject_link.from_contentobject_id ) as count ";
            $objectIDSQL = " AND ezcontentobject_link.from_contentobject_id='$objectId'
                                AND ezcontentobject_link.from_contentobject_version='$objectVersion'";
        }
        // from the following query, this is the excerpted where condition; it causes
        // a BC break in 4.7 and does not seem to do anything ...
        
        //  AND ezcontentobject_link.op_code='0'
        $query = "SELECT $select
                  FROM
                    ezcontentobject, ezcontentobject_link
                  WHERE
                    ezcontentobject.id=ezcontentobject_link.from_contentobject_id AND
                    ezcontentobject.status=" . eZContentObject::STATUS_PUBLISHED . " 
                    
                    $objectIDSQL
                    $relationTypeMasking
                    $showInvisibleNodesCond";
        $rows = $db->arrayQuery( $query );
        return $rows[0]['count'];
    }

    
    //--------------------------------------------------------------------------
    // eep::displayNodeList( $list, $title )
    static function displayNodeList( $list, $title )
    {
        $results[] = array
        (
            "Object"
            , "Node"
            , "Class"
            , "Path"
            , "Ch'n"
            , "Cont"
            , "H/I"
            , "RO"
            , "RRO"
            , "P"
            , "Name"
        );
        foreach( $list as $objectNode )
        {
            $obj = eZContentObject::fetch($objectNode->ContentObjectID);

            $results[] = array
            (
                $objectNode->ContentObjectID
                , $objectNode->NodeID
                , $objectNode->ClassIdentifier
                , $objectNode->PathString
                , $objectNode->childrenCount( false )
                , $objectNode->classIsContainer()
                , $objectNode->IsHidden ."/". $objectNode->IsInvisible
                , eep::fastRelatedObjectCount( $objectNode->ContentObjectID, $objectNode->ContentObjectVersion, eep::NOATTRIBUTE, eep::FORRELATED, array( "IgnoreVisibility"=>true, "AllRelations"=>true) )
                , eep::fastRelatedObjectCount( $objectNode->ContentObjectID, $objectNode->ContentObjectVersion, eep::NOATTRIBUTE, eep::REVRELATED, array( "IgnoreVisibility"=>true, "AllRelations"=>true) )
                , $objectNode->Priority
                , (strlen($objectNode->Name)>20)?substr($objectNode->Name,0,17)."...":$objectNode->Name
            );
        }
        eep::printTable( $results, $title );
    }

    //--------------------------------------------------------------------------
    // a non-object is the array of data that you get when you fetch an object
    // but say that you don't actually want the object
    static function displayNonObjectList( $list, $title )
    {
        $results = array();
        $results[] = array
        (
            "Object"
            , "Sid"
            , "V"
            , "Remote Id"
            , "Name"
        );

        foreach( $list as $lightObject )
        {
            $results[] = array
            (
                $lightObject[ "id" ]
                , $lightObject[ "section_id" ]
                , $lightObject[ "current_version" ]
                , $lightObject[ "remote_id" ]
                , (strlen($lightObject[ "name" ])>60)?substr($lightObject[ "name" ],0,57)."...":$lightObject[ "name" ]
            );
        }
        eep::printTable( $results, $title );
    }

    //--------------------------------------------------------------------------
    static function displayObjectList( $list, $title )
    {
        $results = array();
        $results[] = array
        (
            "Id"
            , "NId"
            , "SId"
            , "V"
            , "Remote Id"
            , "Class Id"
            , "Name"
        );

        foreach( $list as $n => $object )
        {
            $results[] = array
            (
                $object->ID
                , $object->mainNodeID()
                , $object->SectionID
                , $object->CurrentVersion
                , $object->RemoteID
                , $object->contentClassIdentifier()
                , (strlen($object->Name)>60)?substr($object->Name,0,57)."...":$object->Name
            );

            //unset($object);
            //unset( $list[$n] );

            //unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
            //unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
            //unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
        }
        eep::printTable( $results, $title );
    }

    //--------------------------------------------------------------------------
    // returns key value pairs based on any params to the command line that
    // match: --key=value
    static function extractAdditionalParams( &$args )
    {
        $additionalParams = array();
        $regex = "/^--([a-zA-Z0-9\-_\[\]]+)=(.*)/";
        foreach( $args as $key => $arg )
        {
            if( preg_match( $regex, $arg, $matches ) )
            {
                $additionalParams[ $matches[1] ] = $matches[2];
                // since this is an 'additional' parameter, remove it from the
                // argv list so as to prevent it from being interpreted as a
                // regular parameter
                unset( $args[$key] );
            }
        }
        return $additionalParams;
    }

    //--------------------------------------------------------------------------
    static function addTask( $taskType, $task, $priority=500 )
    {
        $priority = (integer )$priority;
        if( 0 == $priority ) return false;

        $db = eZDB::instance();

        // delete any previous instances of this task - this is a performance
        // killer
        // todo, decide if it is worth exposing a switch for this
        /*
        $query = "DELETE FROM onix_cron_daemon";
        $query .= " WHERE ocd_task='" . $task . "'";
        $result = $db->query( $query );
        //*/

        $task = $db->escapeString( $task );
        $taskType = $db->escapeString( $taskType );

        $query = "INSERT into onix_cron_daemon";
        $query .= " SET ocd_priority=" . $priority;
        $query .= " , ocd_task='" . $task . "'";
        $query .= " , ocd_tasktype='" . $taskType . "'";
        $query .= " , ocd_posteddate='" . date("Y-m-d H:i:s") . "'";
        $result = $db->query( $query );

        if( false === $result )
        {
            throw new Exception( "Failed to ad task to cron daemon queue");
        }
    }

    //--------------------------------------------------------------------------
    // this protects against accidentally operating on an object with no main
    // node, ie, an object that is in the trash
    // eep::republishObject( $objectId )
    static function republishObject( $objectId )
    {
        $object = eZContentObject::fetch( $objectId );
        if( $object->attribute( 'main_node' ) )
        {
            $objVersion = $object->currentVersion();
            $publish = eZOperationHandler::execute( "content", "publish",
                array(
                    "object_id"   => $objectId
                    , "version"   => $objVersion->attribute( "version" )
                )
            );
        }
    }

    //--------------------------------------------------------------------------
    // this is just for testing
    static function convertStringToRot13( $str )
    {
        $str = str_rot13( $str );
        return $str;
    }

    //--------------------------------------------------------------------------
    // time is in a colon-separated format hh:mm:ss
    // returns the number of seconds
    static function convertTimeToInteger( $time )
    {
        $timeParts = explode( ":", $time );
        $integer = ($timeParts[0] * 60*60) + ($timeParts[1] * 60) + $timeParts[2];
        if( 0 == strlen( $integer ) )
        {
            // make sure that we return an integer
            $integer = 0;
        }
        return $integer;
    }

    //--------------------------------------------------------------------------
    static function convertTrim( $str )
    {
        $str = trim( $str );
        return $str;
    }
    
    //--------------------------------------------------------------------------
    static function dateToTS( $str )
    {
        $str = strtotime( $str );
        return $str;
    }

    //--------------------------------------------------------------------------
    // this cleans many common forms of XML corruption, and ultimately forces
    // character encoding - this can render some remaining characters as
    // question marks, but many of those can be fixed too
    static function fixXML( $xml )
    {
        $verbose = false;
        if( $verbose )
        {
            echo "\nBEFORE eep::fix()\n";
            echo $xml;
            echo "\n\n";
        }

        // hack to deal with escaped CDATA markers
        $xml = str_replace( array( "&lt;![CDATA[", "]]&gt;" ), array( "<![CDATA[", "]]>" ), $xml );

        // strip the CDATA tags
        $xml = str_replace( array( "<![CDATA[", "]]>" ), array( "", "" ), $xml );

        // fix double escaped ampersands
        $xml = str_replace( "&amp;amp;", "&amp;", $xml );
        // fix double escaped carats
        $xml = str_replace( "&amp;lt;", "&lt;", $xml );
        $xml = str_replace( "&amp;gt;", "&gt;", $xml );
        // fix double escaped decimal entities
        $xml = preg_replace( "/&amp;#([\d]{2,5});/", "&#$1;", $xml );
        // fix double escaped hex entities
        $xml = preg_replace( "/&amp;#x([0-9a-fA-F]{2,5});/", "&#x$1;", $xml );
        // fix double escaped decimal entities with missing semi-colon -- eg &amp;#146
        $xml = preg_replace( "/&amp;#([\d]{2,5})/", "&#$1;", $xml );
        // fix double escaped hex entities with missing semi-colon
        $xml = preg_replace( "/&amp;#x([0-9a-fA-F]{2,5})/", "&#x$1;", $xml );
        // this shows up in NSP data which is important to us
        $xml = str_replace( "compact type=\"disc\"", "", $xml );

        // fix unterminated linebreaks
        $xml = str_replace( "<br>", "<br/>", $xml );
        $xml = str_replace( "<BR>", "<br/>", $xml );

        // fix paras that have extra space between them - which ez puffs into an extra, empty para
        $xml = preg_replace( "/&lt;\/p&gt;[\s]+&lt;p&gt;/", "&lt;/p&gt;&lt;p&gt;", $xml );

        // crazy fancy characters:
        $xml = str_replace( "’", "&#8217;", $xml );
        $xml = str_replace( "•", "&#8226;", $xml );

        // note: this doesn't account for zero-filled hex instances, eg, &#x0021;
        $replacementRules = array
        (
            array
            (   // elipsis
                "new"       => "..."
                , "targets" => array( "&hellip;", "&amp;hellip;", "&#133;", chr(133), "&#x85;" )
            )
            , array
            (   // ndash
                "new"       => "&#8211;"
                , "targets" => array( "&ndash;", "&amp;ndash;", "&#150;", chr(150), "&#x96;", "–" ) /* , "–", "ÔÇô" */
            )
            , array
            (   // left single quote
                "new"       => "&#8216;"
                , "targets" => array( "&lsquo;", "&amp;lsquo;", "&#145;", chr(145), "&#x91;" )
            )
            , array
            (   // right single quote
                "new"       => "&#8217;"
                , "targets" => array( "&rsquo;", "&amp;rsquo;", "&#146;", chr(146), "&#x92;" ) /* , "’" */
            )
            , array
            (   // right double quote
                "new"       => "&#8221;"
                , "targets" => array( "&rdquo;", "&amp;rdquo;", "&#148;", chr(148), "&#x94;", "“" )
            )
            , array
            (   // left double quote
                "new"       => "&#8220;"
                , "targets" => array( "&ldquo;", "&amp;ldquo;", "&#147;", chr(147), "&#x93;", "”" )
            )
            , array
            (   // mdash
                "new"       => "&#8212;"
                , "targets" => array( "&mdash;", "&amp;mdash;", "&#151;", chr(151), "&#x97;", "—" )
            )
            , array
            (   // &eacute;
                "new"       => "&#233;"
                , "targets" => array( "&eacute;" )
            )
            , array
            (   // &ouml;
                "new"       => "&#426;"
                , "targets" => array( "&ouml;" )
            )
        );
        foreach( $replacementRules as $rule )
        {
            $xml = str_replace( $rule["targets"], $rule["new"], $xml );
        }

        // and the ultimate: make damn sure that it's UTF8: this harshly trashes
        // unusual characters BUT it does convert standard usable characters,
        // eg, e ague, into a viable format ALSO, take care to not double encode
        // stuff
        if( "UTF-8" != mb_detect_encoding( $xml, "UTF-8") )
        {
            $xml = utf8_encode( $xml );
        }
        $aDOMDocument = new DOMDocument();
        if( $aDOMDocument->loadXML( $xml ) )
        {
            // pretty sure that we have data that we can work with
        }
        else
        {
            // ok, just force it
            $xml = utf8_encode( $xml );
        }

        if( $verbose )
        {
            echo "\nAFTER eep::fix()\n";
            echo $xml;
            echo "\n\n";
        }
        return $xml;
    }

    //--------------------------------------------------------------------------
    // this is how you fix the bad question marks that have been introduced by
    // forcing the encoding to utf8 -- fix some of the crappy question-mark
    // replacements -- fix the ones that almost certainly mistakes and signs of
    // corruption; count the number of replacements made, and if it seems that
    // there is some question-mark-corruption going on do the fixes to the
    // amibiuous cases
    public static function fixBadQuestionMarks( $xml )
    {
        $count = 0;
        $corruptionLevel = 0;

        // <character>?s
        $xml = preg_replace( "/([a-zA-Z0-9])\?s/", "$1's", $xml, -1, $count );
        $corruptionLevel += $count;

        // <digit>?<digit>
        $xml = preg_replace( "/([\d]+)\?([\d]+)/", "$1&#8211;$2", $xml, -1, $count );
        $corruptionLevel += $count;

        // opening (left) double quote
        $xml = preg_replace( "/\>[\?]([^ ])/", ">&#8220;$1", $xml, -1, $count );
        $corruptionLevel += $count;
        $xml = preg_replace( "/[ ][\?]([^ ])/", " &#8220;$1", $xml, -1, $count );
        $corruptionLevel += $count;

        // mdash between spaces
        $xml = preg_replace( "/[ ][\?][ ]/", " &#8212; ", $xml, -1, $count );
        $corruptionLevel += $count;

        // closing double quote -- although these might be real question marks ...
        if( 4 < $corruptionLevel )
        {
            $xml = preg_replace( "/([^ ])[\?]\</", "$1&#8221;<", $xml, -1, $count );
            $xml = preg_replace( "/([^ ])[\?] /", "$1&#8221; ", $xml, -1, $count );
        }

        if( 6 < $corruptionLevel )
        {
            // mdash between characters -- this might be anything, but almost for sure its not a question mark
            $xml = preg_replace( "/([a-zA-Z0-9])[\?]([a-zA-Z0-9])/", "$1&#8212;$2", $xml, -1, $count );
        }
        return $xml;
    }
}
?>