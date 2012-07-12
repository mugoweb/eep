<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/create/index.php
 */

class create_commands
{
    const create_content   = "content";
    const create_quick     = "quick";

    const fake_text = "Lorem ipsum ei laudem semper sea, mel ne mazim mentitum, nec summo ludus accumsan ea. Has ut illud graecis, an pro aperiri mediocritatem? Nulla putent cotidieque eu mei, te adhuc recteque evertitur mel! Per cu tale aeque percipit? Duo te adhuc copiosae, percipit consectetuer in sed. Eu erant vulputate qui, ne vix ferri vivendum conclusionemque. In ridens ullamcorper has, affert blandit cu cum. Ad sit saepe aliquando maiestatis, consul postea vulputate mei in. Ei sed vero appetere maluisset, graece adipisci comprehensam mea cu. Quo ad dicant legere, ius quodsi inimicus suavitate ad. Puto iuvaret prodesset id sed, option forensibus definitionem vis ad. Malis tempor adipisci ut qui, mei at constituam suscipiantur. Congue facilisis eu mel, debitis volutpat sententiae eos et, quo feugiat expetenda persecuti ea? Omnium appellantur cu pri, adhuc saperet intellegam vel ut. Molestie accusata efficiantur eam ex. Vix an perpetua elaboraret, tota fugit usu ei, mundi aperiam concludaturque ne has. Consulatu vituperatoribus ut eos? Nonumy noluisse indoctum ex his, diam tantas philosophia has ea. Vis te nihil volumus efficiendi? In mea iisque accusata, admodum suscipiantur no mel, facer saepe mnesarchum ut sea. Nam congue conceptam ne! Eum ad legere feugiat, mei omnes vituperata ut. Ea usu nisl facer  dicit, usu stet viris aliquid ex, mel simul solet numquam ex. Ea has nonummy persecuti cotidieque. Inimicus maluisset splendide ei pri, vim novum comprehensam eu. At his quod dicat invenire! Oportere intellegam an usu, ea fabulas principes sea? Probo ullum delicata id per, ut dissentiunt accommodare his! Consul causae feugait eos et, paulo insolens ei sea. At magna lorem eam! Has quodsi corrumpit reformidans no, pri ea stet audiam posidonium. Eu usu congue reformidans, graeci referrentur ei mel. Cu nobis incorrupte mediocritatem mel, illud nihil et cum. Pri saperet antiopam ea. Alia nonummy no mei, quo elit modus id. Ponderum invidunt cu eum. Iriure probatus phaedrum mei ea. Per laudem aliquyam repudiandae ad, eu intellegat constituam ius. Pro ad hendrerit mnesarchum ullamcorper. Vix ea solet adipiscing, nemore salutandi ut per. Ad ferri legimus eleifend sea. Pro posse regione no, eum id quando eruditi, agam timeam vel eu. Sed fugit efficiendi at. Meliore moderatius reprehendunt eu pro. Ei cum virtute dissentias, vel dolores salutandi ei. Pri dolorem omnesque prodesset an, aliquid senserit petentium pro ex. Eius magna rationibus ad duo, no dictas aliquam deterruisset sit? Erat animal postulant et pro, mea ei ubique legere. Vim te dico tritani atomorum, nihil percipit id usu? Pro cu minim essent aliquid, duo ex volumus pertinacia quaerendum, discere inciderint concludaturque in nec! Qui mentitum consulatu in. Euismod vivendum cum et! Ad duo dictas aeterno gloriatur!";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::create_content
        , self::create_quick
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
NOTE: Currently only generates random content based on a given content class and
given parent node.

content
- create content object, fill with random data
- example: for i in {1..10}; do /usr/bin/eep create content random; echo \$i; done
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep use contentnode <parent node id>
  eep create content random
  
quick
  - just create an empty object, return obj id and node id so that the object can be populated
  - the output is, eg:
    new object id 315
    new node id 312

  eep create quick <parent node id> <class identifier>
EOT;
    }

    //--------------------------------------------------------------------------
    // todo: check out: eZContentFunctions::createAndPublishObject( $params ) for help with tricks for creating objects
    public function create( $parentNodeId, $classIdentifier, $dataSet )
    {
        $result = array( "result" => false );

        $parentNode = eZContentObjectTreeNode::fetch( $parentNodeId );
        if( !is_object( $parentNode ) )
        {
            throw new Exception( "createObject::create(): Failed to locate parent node. id='" . $parentNodeId . "'" );
        }

        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "Failed to instantiate content class [" . $classIdentifier . "]" );

        // take care over clustered setups
        $db = eZDB::instance();
        $db->begin();

        $contentObject = $contentClass->instantiate( false, 0, false );
        $dataMap = $contentObject->attribute( 'data_map' );

        foreach( $dataSet as $key => $value )
        {
            // attributes are lower case
            $key = strtolower( $key );
            // if the field exists in the object, write to it
            if( isset( $dataMap[ $key ] )  )
            {
                $dataMap[ $key ]->fromString( $value );
                $dataMap[ $key ]->store();
            }
        }
        $contentObject->store();
        $result[ "objectid" ] = $contentObject->attribute( 'id' );
        $nodeAssignment = eZNodeAssignment::create(
                array(
                    'contentobject_id' => $contentObject->attribute( 'id' ),
                    'contentobject_version' => 1,
                    'parent_node' => $parentNodeId,
                    'is_main' => 1
                    )
                );
        if( !$nodeAssignment )
        {
            throw new Exception( "createObject::create(): Failed to create matching node for object of class '" . $classIdentifier . "'" );
        }

        $nodeAssignment->store();
        $obj_version = $contentObject->currentVersion();
        $publish = eZOperationHandler::execute(
            'content',
            'publish',
            array(
                'object_id' => $contentObject->attribute( 'id' ),
                'version'   => $obj_version->attribute( 'version' ),
            )
        );

        $db->commit();

        $result[ "publish" ] = $publish;
        $result[ "parentnodeid" ] = $parentNodeId;
        $result[ "mainnodeid" ] = $contentObject->mainNodeID();
        $result[ "contentclass" ] = $classIdentifier;
        $result[ "contentobjectid" ] = $contentObject->attribute( 'id' );
        $result[ "result" ] = true;

        return $result;
    }
        
    //--------------------------------------------------------------------------
    private function createContentObject( $classIdentifier, $parentNodeId, $fillMode )
    {
        $contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if( !$contentClass )
            throw new Exception( "This content class does not exist: [" . $classIdentifier . "]" );
        
        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is not an node id: [" .$parentNodeId. "]" );
        
        // todo, in addition to "random" mode, datamap content should be
        // pullable from a suitable xml file; might also provide a way to dump
        // the framework for a content class "as xml"
        if( "random" == $fillMode )
        {
            $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
            $attributeList = eZContentClassAttribute::fetchListByClassID( $classId );
            
            $words = explode( " ", self::fake_text );
            
            $dataSet = array();
            foreach( $attributeList as $attr )
            {
                switch( $attr->DataTypeString )
                {
                    default:
                    case "ezmedia":
                        echo "NOT YET SUPPORTED: [" .$attr->DataTypeString. "]\n";
                        $dataSet[ $attr->Identifier ] = "123";
                        break;

                    case "ezkeyword":
                        $randomKeys = array_flip( array_rand( $words, 2 ) );
                        $randomWords = array_intersect_key( $words, $randomKeys );
                        $dataSet[ $attr->Identifier ] = implode( ",", $randomWords );
                        break;
                    
                    case "ezstring":
                        $randomKeys = array_flip( array_rand( $words, 5 ) );
                        $randomWords = array_intersect_key( $words, $randomKeys );
                        $dataSet[ $attr->Identifier ] = implode( " ", $randomWords );
                        break;

                    case "eztext":
                        $randomKeys = array_flip( array_rand( $words, 100 ) );
                        $randomWords = array_intersect_key( $words, $randomKeys );
                        $dataSet[ $attr->Identifier ] = implode( " ", $randomWords );
                        break;

                    case "ezdatetime":
                        $dataSet[ $attr->Identifier ] = time();
                        break;
                    
                    case "ezxmltext":
                        $text = "";
                        for( $paraCount=0; $paraCount<5; $paraCount+=1 )
                        {
                            $randomKeys = array_flip( array_rand( $words, 60 ) );
                            $randomWords = array_intersect_key( $words, $randomKeys );
                            $text .= "<p>" . implode( " ", $randomWords ) . "</p>";
                        }
                        $parser = new eZOEInputParser();
                        $document = $parser->process( $text );
                        $dataSet[ $attr->Identifier ] = eZXMLTextType::domString( $document );
                        break;
                }
            }        
            $createResults = $this->create( $parentNodeId, $classIdentifier, $dataSet );
            //echo "\nobject creation results\n";
            //var_dump( $createResults );
        }
        else
        {
            throw new Exception( "Only 'random' is currently supported." );
        }
    }
    
    //--------------------------------------------------------------------------
    private function create_quick( $parentNodeId, $classIdentifier )
    {
        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is an invalid parent node id: [" .$parentNodeId. "]" );
        
        $results = $this->create( $parentNodeId, $classIdentifier, array() );
        
        echo "new object id " . $results[ "contentobjectid" ] . "\n";
        echo "new node id " . $results[ "mainnodeid" ] . "\n";
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
            
            case self::create_content:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                $parentNodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                $this->createContentObject( $classIdentifier, $parentNodeId, $param1 );
                break;
            
            case self::create_quick:
                $nodeId = $param1;
                $classIdentifier = $param2;
                $this->create_quick( $nodeId, $classIdentifier );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new create_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>