<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/contentobject/index.php
 */

class contentobject_commands
{
    const contentobject_clearcache              = "clearcache";
    const contentobject_info                    = "info";
    const contentobject_datamap                 = "datamap";
    const contentobject_delete                  = "delete";
    const contentobject_related                 = "related";
    const contentobject_reverserelated          = "reverserelated";
    const contentobject_contentnode             = "contentnode";
    const contentobject_republish               = "republish";
    const contentobject_sitemapxml              = "sitemapxml";
    const contentobject_deleteversions          = "deleteversions";
    const contentobject_fetchbyremoteid         = "fetchbyremoteid";
    const contentobject_setremoteid             = "setremoteid";
    const contentobject_translationcreate       = "translationcreate";
    const contentobject_translationsetmain      = "translationsetmain";
    const contentobject_translationremove       = "translationremove";
    const contentobject_stateassignbyid         = "stateassignbyid";
    const contentobject_stateassignbyidentifier = "stateassignbyidentifier";
    const contentobject_stateview               = "stateview";
    
    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::contentobject_clearcache
        , self::contentobject_contentnode
        , self::contentobject_info
        , self::contentobject_datamap
        , self::contentobject_delete
        , self::contentobject_deleteversions
        , self::contentobject_republish
        , self::contentobject_related
        , self::contentobject_reverserelated
        , self::contentobject_sitemapxml
        , self::contentobject_fetchbyremoteid
        , self::contentobject_setremoteid
        , self::contentobject_translationcreate
        , self::contentobject_translationsetmain
        , self::contentobject_translationremove
        , self::contentobject_stateassignbyid
        , self::contentobject_stateassignbyidentifier
        , self::contentobject_stateview
    );
    var $help = "";                     // used to dump the help string
    
    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );
        
$this->help = <<<EOT
clearcache
- clear the content cache for given object
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject clearcache
  or
  eep contentobject clearcache <object id>

contentnode
- convert a content object id into a content node id
  eep use ezroot <path>
  eep contentobject contentnode <content object id>
  or
  eep use ezroot <path>
  eep use contentobject <content object id>
  eep contentobject contentnode

datamap
- dumps most of the datamap
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject datamap
  or
  eep contentobject datamap <object id>

delete
- deletes an object and it's children
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject delete
  or
  eep contentobject delete <object id>

deleteversions
- deletes all the archived versions of an object
  eep contentobject deleteversions <object id>

info
- dumps some info about the content object
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject info
  or
  eep contentobject info <object id>

fetchbyremoteid
  eep use ezroot <path>
  eep contentobject fetchbyremoteid <remoteid>
  
republish
- republishes an object
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject republish
  or
  eep contentobject republish <object id>

related
- dumps list of related objects
- supports use of --limit=n and --offset=m
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject related
  or
  eep contentobject related <object id>

reverserelated
- dumps list of reverserelated objects
- supports use of --limit=n and --offset=m
  eep use ezroot <path>
  eep use contentobject <object id>
  eep contentobject reverserelated
  or
  eep contentobject reverserelated <object id>
  
setremoteid
  eep use ezroot <path>
  eep contentobject setremoteid <object id> <remoteid>
  note that only [a-zA-Z0-0_] are valid characters

sitemapxml
- emit line of xml for inclusion in a sitemap
- note domain is only: example.com
- note <change frequency> is one of: always hourly daily weekly monthly yearly never
- note that the sitemap header is: <?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
-           the matching close is: </urlset>
  eep contentobject sitemapxml <object id> <domain> [<change frequency> [<priority>]]
  
translationcreate
- add a new translation for the content object, optionally copy the translation from an existing one
  note that 'locale's are, eg., eng-GB or eng-US
  eep contentobject translationcreate <object id> <new locale> [<existing locale>]
  
translationsetmain
- set the main translation, eg. in preparation to removing eng-GB as a supported translation
  eep contentobject translationsetmain <object id> <locale>
  
translationremove
- remove a translation from the content object
  eep contentobject translationremove <object id> <locale>
  
stateassignbyid
- assign an object state by state id
  eep contentobject stateassignbyid <object id> <state id>
  
stateassignbyidentifier
- assign an object state by state/group identifier e.g. ez_lock/locked
  all state groups and states can be found in the admin interface: Setup > States
  eep contentobject stateassignbyidentifier <object id> <state/group identifier>
  
stateview
- view object state information
  eep contentobject stateview <object id>
EOT;
    }

    //--------------------------------------------------------------------------
    private function dumpContentObjectInfo( &$contentObject )
    {
        $keepers = array
        (
            "Name"
            , "CurrentLanguage"
            , "ClassIdentifier"
            // wtf , "StateIDArray":"eZContentObject":private
            , "SectionID"
            , "OwnerID"
            , "Published"
            , "Modified"
            , "CurrentVersion"
            , "Status"
            , "RemoteID"
            //, "DataMap"  
        );

        $results[] = array( "key",      "value" );
        foreach( $keepers as $key )
        {
            $value = $contentObject->$key;
            // fix false as empty string
            if( false === $value )
            {
                $value = "(false)";
            }
            // fix timestamps
            if( in_array( $key, array("Published","Modified") ) )
            {
                $value = $value . " (".date("Y-m-d H:i:s",$value).")";
            }
            $results[] = array( $key, $value );
        }
        // other values ...
        $results[] = array( "MainNodeID", $contentObject->mainNodeID() );
        // additional locations, only show if there is more than the main node
        $assignedNodes = $contentObject->attribute( 'assigned_nodes' );
        //var_dump( $assignedNodes );
        if( 0 < count($assignedNodes ) )
        {
            $results[] = array( "", "" );
            $results[] = array( "All locations:", "" );
            foreach( $assignedNodes as $otherLocation )
            {
                $results[] = array( $otherLocation->PathString, $otherLocation->PathIdentificationString );
            }
        }

        eep::printTable( $results, "contentobject id [" .$contentObject->ID. "]" );
    }
    
    //--------------------------------------------------------------------------
    private function fetchContentObjectFromId( $contentobjectId )
    {
        $contentobject = eZContentObject::fetch( $contentobjectId );
        $this->dumpContentObjectInfo( $contentobject );
    }

    //--------------------------------------------------------------------------
    private function fetchDataMapFromId( $contentobjectId )
    {
        $contentobject = eZContentObject::fetch( $contentobjectId );
        $dataMap = $contentobject->dataMap();
        
        $results[] = array
        (
            "identifier"
            , "ID"
            , "DataTypeString"
            , "DataText"
            , "DataInt"
            , "DataFloat"
            , "SortKeyInt"
            , "SortKeyString"
            , "Ver."
        );
        foreach( $dataMap as $name => $attr )
        {
            $results[] = array
            (
                $name
                , $attr->ID
                , $attr->DataTypeString
                , (35<strlen($attr->DataText))?substr( $attr->DataText, 0, 35 )."...":$attr->DataText
                , $attr->DataInt
                , $attr->DataFloat
                , $attr->SortKeyInt
                , (15<strlen($attr->SortKeyString))?substr( $attr->SortKeyString, 0, 15 )."...":$attr->SortKeyString
                , $attr->Version
            );
        }
        eep::printTable( $results, "contentobject datamap id [" .$contentobjectId. "]" );
    }
    
    //--------------------------------------------------------------------------
    private function fetchRelated( $objectId, $reverse, $additional )
    {
        $object = eZContentObject::fetch( $objectId );

        // some other parameters:
        // LoadDataMap
        // Limit
        // Offset
        // AsObject
        // SortBy
        // IgnoreVisibility
        $parameters = array();
        $parameters[ "AllRelations" ] = true;
        $parameters[ "IgnoreVisibility" ] = true;
        if( isset($additional["limit"]) )
        {
            $parameters[ "Limit" ] = $additional["limit"];
        }
        if( isset($additional["offset"]) )
        {
            $parameters[ "Offset" ] = $additional["offset"];
        }
        
        $reverseRelated = $object->relatedObjects
        (
            false                           // use current version of object
            , false                         // use current object id
            , 0                             // attribute id, but we are going to use 'all relations' instead
            , false                         // return array of objects or a grouped list ... ?
            , $parameters
            , $reverse                      // true->reverse-related and false->related
        );
        
        $keepers = array
        (
            "ObjectID"
            , "MainNodeID"
            , "ClassIdentifier"
            //, "StateIDArray"
            , "SID"
            , "Name"
        );
        
        $results[] = $keepers;
        $rowCount = 0;
        foreach( $reverseRelated as $revObject )
        {
            $row = array
            (
                $revObject->ID
                , $revObject->mainNodeId()
                , $revObject->ClassIdentifier
                //, serialize( $revObject->stateIdentifierArray() )
                , $revObject->SectionID
                , $revObject->Name
            );
            $results[] = $row;
            $rowCount++;
        }

        $methodPrefix = "Reverse related";
		if( !$reverse )
		{
			$methodPrefix = "Related";
		}
        eep::printTable( $results, $methodPrefix . " objects of oid: " .$objectId. " count: " . $rowCount . "" );
    }

    // todo, this does not return the full list of reverse related stuff
    private function fetchReverseRelated( $objectId, $additional )
    {
        return self::fetchRelated( $objectId, true, $additional );
    }
    
    //--------------------------------------------------------------------------
    private function delete( $objectId )
    {
        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        $adminUserObject->loginCurrent();
        // the default behaviour is to delete the subtree, 
        // FYI, purge(), the non-default behaviour, leaves all the children in place, but up 1 level, and does not update their node-paths ... that is, it corrupts the content structure
        $result = eZContentObjectOperations::remove( $objectId ); 
        $adminUserObject->logoutCurrent();
        if( $result )
        {
            echo "Deleted " . $objectId . "\n";
        }
        else
        {
            echo "Failed to delete " .$objectId. "\n";
        }
    }
    
    //--------------------------------------------------------------------------
    private function convertToNodeId( $objectId )
    {
        $object = eZContentObject::fetch( $objectId, true );
        return $object->MainNodeId();
    }
    
    //--------------------------------------------------------------------------
    private function clearObjectCache( $objectId )
    {
        // todo: the first one is suspect:
        //eZContentObject::clearCache( array( $objectId ) );
        
        // ... so, todo: does this work any better?
        eZContentCacheManager::clearContentCacheIfNeeded( $objectId );
    }
    
    //--------------------------------------------------------------------------
    // <url>
    // <loc>http://www.example.com/</loc>
    // <lastmod>2005-01-01</lastmod>
    // <changefreq>monthly</changefreq>
    // <priority>0.8</priority>
    // </url>
    private function sitemapxml( $objectId, $domain, $changeFrequency, $priority )
    {
        $object = eZContentObject::fetch( $objectId, true );
        $node = eZContentObjectTreeNode::fetch( $object->MainNodeId() );
        $pathNodes = explode( "/", $node->PathString );
        array_shift( $pathNodes );
        array_shift( $pathNodes );
        array_shift( $pathNodes );
        array_pop( $pathNodes );
        
        $location = "http://" . $domain;
        foreach( $pathNodes as $nodeId )
        {
            $node = eZContentObjectTreeNode::fetch( $nodeId );
            $location .= "/" . $node->pathWithNames( true );
        }
        $lastModified = date( DATE_ATOM, $object->Modified ); // 1997-07-16T19:20:30.45+01:00

        // ["Modified"]=>string(10) "1270075155"
        // ["Published"]=>string(10) "1270075155"
        // hourly daily weekly monthly yearly never
        
        if( !$changeFrequency )
        {
            $changeFrequency  = "weekly";
        }
        if( !$priority )
        {
            $priority = 0.5;
        }
        $xml = "<url>";
        $xml .= "<loc>" . $location . "</loc>";
        $xml .= "<lastmod>" . $lastModified . "</lastmod>";
        $xml .= "<changefreq>" . $changeFrequency . "</changefreq>";
        $xml .= "<priority>" .$priority. "</priority>";
        $xml .= "</url>\n";
        
        echo $xml;
    }
    
    //--------------------------------------------------------------------------
    private function deleteversions( $objectId )
    {
        $contentObject = eZContentObject::fetch( $objectId );
        $versionCount = $contentObject->getVersionCount();
        $params = array( 'conditions'=> array( 'status' => eZContentObjectVersion::STATUS_ARCHIVED ) );
        $versions = $contentObject->versions( true, $params );
        if( count( $versions ) > 0 )
        {
            echo "Deleting ". count( $versions ) . " versions\n";
            foreach( $versions as $version )
            {
                $version->removeThis();
            }
        }
    }
    
    //--------------------------------------------------------------------------
    private function fetchbyremoteid( $remoteId )
    {
        $contentObject = eZContentObject::fetchByRemoteID( $remoteId );
        $this->dumpContentObjectInfo( $contentObject );
    }

    //--------------------------------------------------------------------------
    private function setremoteid( $objectId, $remoteId )
    {
        $contentObject = eZContentObject::fetch( $objectId );
        $contentObject->setAttribute( 'remote_id',  $remoteId );
        $contentObject->sync( array( 'remote_id' ) );
    }
    
    //--------------------------------------------------------------------------
    private function translationcreate( $objectId, $newLocale, $sourceLocale = false )
    {
        $contentObject = eZContentObject::fetch( $objectId );

        $newLocalId = false; // just using this for the validation step
        // validate or create the new locale
        $languageList = eZContentLanguage::fetchList( true /*force reload*/ );
        foreach( $languageList as $languageId => $eZContentLanguage )
        {
            if( $newLocale == $eZContentLanguage->Locale )
            {
                $newLocalId = $languageId;
            }
        }
        if( !($newLocalId > 0) )
        {
            throw new Exception( "Failed to locate locale $newLocale" );
        }

        $copyFromLanguageCode = false;
        // todo, should validate the $sourceLocale somehow ... make sure that the content object does have that translation?
        if( $sourceLocale )
        {
            $copyFromLanguageCode = $sourceLocale;
        }

        $newVersion = $contentObject->createNewVersion( $contentObject->CurrentVersion, true, $newLocale, $sourceLocale );

        eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObject->ID, 'version' => $newVersion->Version ) );

        echo "Added translation $newLocale to $contentObject->ID\n";
    }
    
    //--------------------------------------------------------------------------
    private function translationsetmain( $objectId, $locale )
    {
        $contentObject = eZContentObject::fetch( $objectId );
        $mainNodeId = $contentObject->attribute('main_node_id');
        $language = eZContentLanguage::fetchByLocale( $locale );

        if( !$language->ID )
        {
            throw new Exception( "Failed to locate language for locale $locale" );
        }

        $success = false;
        // from eZContentOperationCollection::updateInitialLanguage(), which unfortunately always returns
        // a positive response, so we use the code directly
        if ( $language and !$language->attribute( 'disabled' ) )
        {
            $contentObject->setAttribute( 'initial_language_id', $language->ID );
            $objectName = $contentObject->name( false, $language->attribute( 'locale' ) );
            $contentObject->setAttribute( 'name', $objectName );
            $contentObject->store();

            $success = true;

            if ( $contentObject->isAlwaysAvailable() )
            {
                $contentObject->setAlwaysAvailableLanguageID( $language->ID );
            }

            $nodes = $contentObject->assignedNodes();
            foreach ( $nodes as $node )
            {
                $node->updateSubTreePath();
            }
        }

        eZContentCacheManager::clearContentCacheIfNeeded( $objectId );

        if( $success )
        {
            echo "Set main locale $locale with id $language->ID \n";
        }
        else
        {
            echo "Failed to locate locale $locale did not set main.\n";
        }
    }
    
    //--------------------------------------------------------------------------
    private function translationremove( $objectId, $locale )
    {
        $contentObject = eZContentObject::fetch( $objectId );
        $language = eZContentLanguage::fetchByLocale( $locale );

        if( !$language->ID )
        {
            throw new Exception( "Failed to locate language for locale: [ $locale ]" );
        }

        $adminUserObject = eZUser::fetch( eepSetting::PrivilegedAccountId );
        if( null === $adminUserObject )
        {
            throw new Exception( "eepSetting::PrivilegedAccountId value of " . eepSetting::PrivilegedAccountId . " is invalid. Exiting." );
        }
        $adminUserObject->loginCurrent();

        // from eZContentOperationCollection::removeTranslation(), which unfortunately always returns
        // a positive response, so we use the code directly
        if ( $contentObject->removeTranslation( $language->ID ) )
        {
            echo "Removed the translation $locale with language id $language->ID from $objectId\n";
        }
        else
        {
            echo "Failed to remove the translation $locale with language id $language->ID from $objectId\n";
        }
        $adminUserObject->logoutCurrent();

        eZContentOperationCollection::registerSearchObject( $objectId );

        eZContentCacheManager::clearContentCacheIfNeeded( $objectId );
    }

    //--------------------------------------------------------------------------
    private function stateassignbyidentifier( $objectId, $stateGroupIdentifier )
    {
        $identifierParts = explode( '/', $stateGroupIdentifier ); // group_identifier/state_identifier

        if( !$identifierParts || 2 !== count( $identifierParts ) )
        {
            throw new Exception( "Invalid state/group identifier $stateGroupIdentifier" );
        }

        $group = eZContentObjectStateGroup::fetchByIdentifier( $identifierParts[0] );
        if( !$group->Identifier )
        {
            throw new Exception( "Failed to locate state group $identifierParts[0]" );
        }

        $state = eZContentObjectState::fetchByIdentifier( $identifierParts[1], $group->ID );
        if( !$state->Identifier )
        {
            throw new Exception( "Failed to locate state $identifierParts[1] in group $identifierParts[0]" );
        }

        $this->stateassign( $objectId, $state );
    }

    //--------------------------------------------------------------------------
    private function stateassignbyid( $objectId, $stateId )
    {
        $state = eZContentObjectState::fetchById( $stateId );

        if( !$state->ID )
        {
            throw new Exception( "Failed to locate state id $stateId" );
        }

        $this->stateassign( $objectId, $state );
    }

    //--------------------------------------------------------------------------
    private function stateassign( $objectId, $state )
    {
        // from eZContentOperationCollection::updateObjectState(), which unfortunately always returns
        // a positive response, so we use the code directly
        $object = eZContentObject::fetch( $objectId );

        if( !$state->Identifier )
        {
            throw new Exception( "Failed to locate state" );
        }

        if ( $object->assignState( $state ) )
        {
            echo "Assigned state $state->Identifier with id $state->ID and group id $state->GroupID to $objectId\n";
        }
        else
        {
            echo "Failed to assign state $state->Identifier with id $state->ID and group id $state->GroupID to $objectId\n";
        }
        // call appropriate method from search engine
        eZSearch::updateObjectState( $objectId, array( $state->ID ) );

        eZContentCacheManager::clearContentCacheIfNeeded( $objectId );
    }

    //--------------------------------------------------------------------------
    private function stateview( $objectId )
    {
        $object = eZContentObject::fetch( $objectId );

        $results[] = array
        (
            'Id'
            , 'Identifier'
            , 'Group Id'
            , 'Group Identifier'
            , 'Name'
        );

        $stateIds = $object->attribute( 'state_id_array' );

        foreach ( $stateIds as $stateId )
        {
            $state = eZContentObjectState::fetchById( $stateId );
            $group = $state->attribute( 'group' );

            $results[] = array
            (
                $state->attribute( 'id' )
                , $state->attribute( 'identifier' )
                , $state->attribute( 'group_id' )
                , $group->attribute( 'identifier' )
                , $state->attribute('current_translation')->Name
            );
        }

        eep::printTable( $results, "States of oid: $objectId" );
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
            
            case self::contentobject_info:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->fetchContentObjectFromId( $objectId );
                break;
            
            case self::contentobject_datamap:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->fetchDataMapFromId( $objectId );
                break;
            
            case self::contentobject_related: 
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->fetchRelated( $objectId, false, $additional );
                break;

            case self::contentobject_reverserelated:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->fetchReverseRelated( $objectId, $additional );
                break;
            
            case self::contentobject_delete:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->delete( $objectId );
                break;
            
            case self::contentobject_contentnode:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                echo $this->convertToNodeId( $objectId ) . "\n";
                break;
            
            case self::contentobject_republish:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                eep::republishObject( $objectId );
                echo "republished " . $objectId . "\n";
                break;
            
            case self::contentobject_clearcache:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->clearObjectCache( $objectId );
                break;
            
            case self::contentobject_sitemapxml:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->sitemapxml( $objectId, $param2, $param3, $param4 ); // objid, domain, change-frequency, priority
                break;
            
            case self::contentobject_deleteversions:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                $this->deleteversions( $objectId );
                break;
            
            case self::contentobject_fetchbyremoteid:
                $this->fetchbyremoteid( $param1 );
                break;
            
            case self::contentobject_setremoteid:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                $remoteId = $param2;
                if( !eepValidate::validateContentObjectId( $objectId ) )
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                if( preg_replace( "/[^a-zA-Z0-0_]/", "", $remoteId ) != $remoteId )
                    throw new Exception( "This is not an acceptable remote id: [" .$remoteId. "]" );
                $this->setremoteid( $objectId, $remoteId );
                break;

            case self::contentobject_translationcreate:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->translationcreate( $objectId, $param2, $param3 );
                break;

            case self::contentobject_translationsetmain:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->translationsetmain( $objectId, $param2 );
                break;

            case self::contentobject_translationremove:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->translationremove( $objectId, $param2 );
                break;

            case self::contentobject_stateassignbyid:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->stateassignbyid( $objectId, $param2 );
                break;

            case self::contentobject_stateassignbyidentifier:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->stateassignbyidentifier( $objectId, $param2, $param3 );
                break;

            case self::contentobject_stateview:
                $objectId = $eepCache->readFromCache( eepCache::use_key_object );
                if( $param1 )
                {
                    $objectId = $param1;
                }
                if( !eepValidate::validateContentObjectId( $objectId ) )
                {
                    throw new Exception( "This is not an object id: [" .$objectId. "]" );
                }
                $this->stateview( $objectId );
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new contentobject_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>
