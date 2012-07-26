<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * eep/modules/list/index.php
 *
 *
 *  todo, for a nice way to handle attribute creation, look at:
 *  /home/dfp/http/44tester/kernel/classes/packagehandlers/ezcontentclass/ezcontentclasspackagehandler.php
 *  centered around the eZContentClassAttribute::create() call
 *
 *  todo list modules, indicate where they are declared
 *
 *  todo list template operators, indicate where they are declared
 *
 *  todo list ini files on a per-siteaccess basis
 *       maybe, list them on a per extension basis
 *       are there other bases to list ini files?
 *       custom
 *
 */

class list_commands
{
    const list_contentclasses       = "contentclasses";
    const list_attributes           = "attributes";
    const list_all_attributes       = "allattributes";
    const list_children             = "children";
    const list_siteaccesses         = "siteaccesses";
    const list_allinifiles          = "allinifiles";
    const list_subtree              = "subtree";
    const list_extensions           = "extensions";

    //--------------------------------------------------------------------------
    var $availableCommands = array
    (
        "help"
        , self::list_attributes
        , self::list_all_attributes
        , self::list_children
        , self::list_contentclasses
        , self::list_extensions
        , self::list_allinifiles
        , self::list_siteaccesses
        , self::list_subtree
    );
    var $help = "";                     // used to dump the help string

    //--------------------------------------------------------------------------
    public function __construct()
    {
        $parts = explode( "/", __FILE__ );
        array_pop( $parts );
        $command = array_pop( $parts );

$this->help = <<<EOT
attributes
- list attributes of class
  eep use ezroot <path>
  eep use contentclass <class identifier>
  eep list attributes
  or
  eep list attributes <class identifier>

allattributes
- list all attributes present in the system
  eep use ezroot <path>
  eep list allattributes

children
- list children of node
- supports --limit=N and/or --offset=M
  eep use ezroot <path>
  eep use contentnode <node id>
  eep list children [--offset=<N>] [--limit=<M>]
  or
  eep list children <node id> [--offset=<N>] [--limit=<M>]

contentclasses
- list all content classess
  eep use ezroot <path>
  eep list contentclasses

extensions
- list all the extensions
  eep use ezroot <path>
  eep list extensions

allinifiles
- list all inifiles
  eep use ezroot <path>
  eep list inifiles

siteaccesses
- list all siteaccesses
  eep use ezroot <path>
  eep list siteaccesses

subtree
- list all the nodes in a subtree
  supports --limit and --offset
  eep use ezroot <path>
  eep use contentnode <node id>
  eep list subtree
  or
  eep list subtree <node id>
EOT;
    }

    //--------------------------------------------------------------------------
    private function countObjectsPerClassId( $Id )
    {
        // could probably use and API call too:
        // $listCount = eZContentObject::fetchSameClassListCount( 52 );
        $db = eZDB::instance();
        $countRow = $db->arrayQuery( 'SELECT count(*) AS count FROM ezcontentobject WHERE contentclass_id='. $Id ." and status = " . eZContentObject::STATUS_PUBLISHED );
        return $countRow[0]['count'];
    }

    //--------------------------------------------------------------------------
    private function listContentClasses()
    {
        $contentClassList = eZContentClass::fetchAllClasses( true, false, false );
        $results = array();
        $results[] = array
        (
            "Identifier"
            , "Id"
            , "#"
            , "RemoteID"
            , "Lang"
            , "Name"
            , "Group"
        );
        foreach( $contentClassList as $classInfo )
        {
            $classId    = $classInfo->ID;
            $groupList  = $classInfo->fetchGroupList();
            $groupNames = array();
            foreach( $groupList as $groupName )
            {
                array_push( $groupNames, $groupName->GroupName);
            }
            $classInstance = eZContentClass::fetch( $classId );
            $snl = unserialize( $classInstance->SerializedNameList );
            $results[] = array
            (
                $classInstance->Identifier
                , $classId
                , $this->countObjectsPerClassId( $classId )
                , $classInstance->RemoteID
                , $snl[ "always-available" ]
                , $snl[ $snl[ "always-available" ] ]
                , implode(",",$groupNames)
            );
        }
        eep::printTable( $results, "list content classes" );
    }

    private function listAllAttributes()
    {
        $attributeList = @eZContentClassAttribute::fetchList();
        $contentClassList   = eZContentClass::fetchAllClasses( true, false, false );
        $classDictionary    = array();
        foreach( $contentClassList as $classInfo )
        {
            $classId    = $classInfo->ID;
            $classDictionary[$classInfo->ID] = $classInfo->Identifier;
        }

        $results = array();
        $results[] = array
        (
            "ID"
            , "Identifier"
            , "Data Type"
            , "Content Class ID"
            , "Content class identifier"
        );

        foreach( $attributeList as $attributeInfo )
        {
            $attributeId    = $attributeInfo->ID;
            $ContentClassID = $attributeInfo->ContentClassID;
            $results[] = array
            (
                $attributeId
                , $attributeInfo->Identifier
                , $attributeInfo->DataTypeString
                , $attributeInfo->ContentClassID
                , (isset($classDictionary[$attributeInfo->ContentClassID]))?$classDictionary[$attributeInfo->ContentClassID]:"Doesn't exist"
            );
        }

        eep::printTable( $results, "list content classes" );
    }

    //--------------------------------------------------------------------------
    private function listChildNodes( $parentNodeId, $additional )
    {
        if( !eepValidate::validateContentNodeId( $parentNodeId ) )
            throw new Exception( "This is not an node id: [" .$parentNodeId. "]" );

        $limit = 100;
        if( isset($additional["limit"]) )
        {
            $limit = $additional["limit"];
        }
        $offset = 0;
        if( isset($additional["offset"]) )
        {
            $offset = $additional["offset"];
        }

        $params[ "Depth" ] = 1;
        //$parms[ "MainNodeOnly" ] = true;
        $params[ "IgnoreVisibility" ] = true;
        $params[ 'Limitation' ] = array();
        $params[ 'Limit' ] = $limit;
        $params[ 'Offset' ] = $offset;

        $children = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentNodeId );

        $numberOfChildrenFetched = count( $children );
        $parentNode = eZContentObjectTreeNode::fetch( $parentNodeId );
        $pathToParent = $parentNode->PathString;
        $pathToParent = explode( "/", $pathToParent );
        array_pop( $pathToParent );
        array_pop( $pathToParent );
        $pathToParent = implode( "/", $pathToParent ) . "/";
        $title = $numberOfChildrenFetched." children of node: ".$parentNodeId." [".$pathToParent."]";

        eep::displayNodeList( $children, $title );
    }

    //--------------------------------------------------------------------------
    private function listSiteAccesses()
    {
        $definedByFolder = array();

        // get all the siteaccess folders from /settings/siteaccess
        $settingsPath = "./settings/siteaccess";
        $hDir = opendir( $settingsPath );
        $file = readdir( $hDir );
        while( false != $file )
        {
            if( !in_array( $file , array(".", "..", ".svn") ))
            {
                if( is_dir( $settingsPath ."/". $file ) )
                    $definedByFolder[] = $file;
            }
            $file = readdir( $hDir );
        }

        // look for weird siteaccess folder inside an extension, so loop through
        // all the extensions and include in the list any folders in
        // <extn>/settings/siteaccess/ ...
        $extensionSiteAccess = array();
        $siteINI = eZINI::instance( "site.ini" );
        $extensionPath = "./" . $siteINI->variable( "ExtensionSettings", "ExtensionDirectory" );
        $hDir = opendir( $extensionPath );
        $file = readdir( $hDir );
        while( false != $file )
        {
            if( !in_array( $file , array(".", "..", ".svn") ))
            {
                if( is_dir( $extensionPath ."/". $file ) )
                {
                    // this is an extension, now look for a siteaccess folder
                    $siteAccessPath = $extensionPath ."/". $file . "/settings/siteaccess";
                    if( is_dir( $siteAccessPath ) )
                    {
                        // any folders here are extension-defined siteaccesses
                        $hSubDir = opendir( $siteAccessPath );
                        $subFile = readdir( $hSubDir );
                        while( false != $subFile )
                        {
                            if( !in_array( $subFile , array(".", "..", ".svn") ))
                            {
                                if( is_dir( $siteAccessPath ."/". $subFile ) )
                                {
                                    $definedByFolder[] = $subFile;
                                    $extensionSiteAccess[$subFile] = $file;
                                }
                            }
                            $subFile = readdir( $hDir );
                        }
                    }
                }
            }
            $file = readdir( $hDir );
        }
        // for each of the accumulated siteaccess folders, indicate if and where
        // the siteaccess is activated from
        $siteINI = eZINI::instance( "site.ini" );
        $available = $siteINI->variable( "SiteAccessSettings", "AvailableSiteAccessList" );
        $related = $siteINI->variable( "SiteAccessSettings", "RelatedSiteAccessList" );

        $all = array_merge( $definedByFolder, $available, $related );
        $all = array_unique( $all );
        sort( $all );

        $results[] = array
        (
            "folders"
            , "AvailableSiteAccessList"
            , "RelatedSiteAccessList"
            , "defined in extension"
        );

        foreach( $all as $n => $sa )
        {
            $results[ 1 + $n ][ 0 ] = in_array( $sa, $definedByFolder ) ? $sa : "";
            $results[ 1 + $n ][ 1 ] = in_array( $sa, $available ) ? $sa : "";
            $results[ 1 + $n ][ 2 ] = in_array( $sa, $related ) ? $sa : "";
            $results[ 1 + $n ][ 3 ] = isset( $extensionSiteAccess[$sa] ) ? $extensionSiteAccess[$sa] : "";
            in_array( $sa, $related ) ? $sa : "";
        }

        eep::printTable( $results, "site accesses: folders, active and 'on same db'" );
    }

    //--------------------------------------------------------------------------
    private function findIniFilePerDirectory( $fullPath )
    {
        echo $fullPath;

        $files = array();
        $hDir = opendir( $fullPath );
        $file = readdir( $hDir );
        while( false != $file )
        {
            if( !in_array( $file , array(".", "..", ".svn") ))
            {
                if( !is_dir( $settingsPath ."/". $file ) )
                {
                    if( preg_match( "/.ini$/", $file ) || preg_match( "/.ini.append.php$/", $file ) )
                    {
                        $files[] = $file;
                    }
                }
            }
            $file = readdir( $hDir );
        }
        return $files;
    }

    //--------------------------------------------------------------------------
    /*
     so here's the problem:
     there are a bunch of files that have the same root:
     site.ini
     site.ini.append.php
     site.ini.append.php_PROD
     site.ini.append.php_TEST

     etc. The last 3 might all exist in the same folder.

     Do you:
     (a) simply keep track of a single instance in the folder of "site" ?
     (b) or do you try to indicate that there are several versions of "site" in there, somehow?

     Supposing (a), you probably want to focus on the file with the most-correct
     name, either "site.ini" or "site.ini.append.php".

     Which implies that you simply want to ignore any file that doesn't have a filename
     that fits the pattern.

    */
    //--------------------------------------------------------------------------
    private function appendIniInfo( $key, $path, $filename, $fileList )
    {
        $patternIni = "/([^.]+)\.(.*)/";
        $validExtensions = array( "ini", "ini.append.php" );

        $nameMatch = preg_match( $patternIni, $filename, $matches );
        if( 0 < $nameMatch )
        {
            if( in_array( $matches[2], $validExtensions ) )
            {
                //echo "matched:".$filename."\n";
                //var_dump($matches);
                if( "file" == filetype( $path.$filename ) )
                {
                    $fileList[ $matches[1] ][ $key ] = "file";
                }
                elseif( "link" == filetype( $path.$filename) )
                {
                    $fileList[ $matches[1] ][ $key ] = "link";
                }
            }
        }
        return $fileList;
    }

    //--------------------------------------------------------------------------
    private function allinifiles()
    {
        $eepCache = eepCache::getInstance();
        $fileList = array();

        $instanceRoot = $eepCache->readfromCache( eepCache::use_key_ezroot );

        // get the default ini's from settings folder:
        $path = $instanceRoot . "/settings/";
        $dirh = opendir( $path );
        $file = readdir( $dirh );
        while( false !== $file )
        {
            $fileList = $this->appendIniInfo( "settings", $path, $file, $fileList );
            $file = readdir( $dirh );
        }
        closedir( $dirh );

        // get the default ini's from settings/override folder:
        $path = $instanceRoot . "/settings/override/";
        $dirh = opendir( $path );
        $file = readdir( $dirh );
        while( false !== $file )
        {
            $fileList = $this->appendIniInfo( "override", $path, $file, $fileList );
            $file = readdir( $dirh );
        }
        closedir( $dirh );

        // get all the siteaccess folders
        $siteaccessFolders = array();
        $path = $instanceRoot . "/settings/siteaccess/";
        $dirh = opendir( $path );
        $file = readdir( $dirh );
        while( false !== $file )
        {
            //echo "filename: $file : filetype: " . filetype( $path . $file) . "\n";
            if( "dir" == filetype($path . $file) && "." != $file[0] )
            {
                $pathInfo = pathinfo($path . $file);
                $siteaccessFolders[] = $file;
            }
            $file = readdir( $dirh );
        }
        closedir( $dirh );
        // get files from all the siteaccess folders
        foreach( $siteaccessFolders as $siteaccessFolder )
        {
            $path = $instanceRoot . "/settings/siteaccess/" . $siteaccessFolder . "/";
            $dirh = opendir( $path );
            $file = readdir( $dirh );
            while( false !== $file )
            {
                $fileList = $this->appendIniInfo( $siteaccessFolder, $path, $file, $fileList );
                $file = readdir( $dirh );
            }
            closedir( $dirh );
        }

        // get all the extension folders, assume the usual path (so that we don't have to do an ini lookup)
        $extensionFolders = array();
        $extensionPath = $instanceRoot . "/extension/";
        $dirh = opendir( $extensionPath );
        $file = readdir( $dirh );
        while( false != $file )
        {
            if( "dir" == filetype( $extensionPath.$file) && "." != $file[0] )
            {
                $extensionFolders[] = $file;
            }
            $file = readdir( $dirh );
        }
        closedir( $dirh );

        // add all the settings from the extension's settings folder
        foreach( $extensionFolders as $extensionFolder )
        {
            $path = $instanceRoot ."/extension/". $extensionFolder . "/settings/";
            $dirh = @opendir( $path );
            if( false !== $dirh )
            {
                $file = readdir( $dirh );
                while( false !== $file )
                {
                    $fileList = $this->appendIniInfo( $extensionFolder, $path, $file, $fileList );
                    $file = readdir( $dirh );
                }
                closedir( $dirh );
            }
        }

        // generate the output
        $results[] = array
        (
            "ini file"
            , "/settings"
            , "/override"
        );
        foreach( $siteaccessFolders as $siteaccess )
        {
            $results[0][] = $siteaccess;
        }
        foreach( $extensionFolders as $extension )
        {
            $results[0][] = $extension;
        }
        ksort( $fileList );
        foreach( $fileList as $ini => $details )
        {
            $row = array();
            $row[] = $ini;
            $row[] = @$details[ "settings" ];
            $row[] = @$details[ "override" ];
            foreach( $siteaccessFolders as $sa )
            {
                $row[] = @$details[ $sa ];
            }
            foreach( $extensionFolders as $ex )
            {
                $row[] = @$details[ $ex ];
            }
            $results[] = $row;
        }
        eep::printTable( $results, "list ini files" );
    }

    //--------------------------------------------------------------------------
    private function listSubtree( $subtreeNodeId, $additional )
    {
        $title = "All nodes in subtree [" .$subtreeNodeId. "]";

        $params[ "Depth" ] = 0;
        $params[ "IgnoreVisibility" ] = true;
        $params[ "Limitation" ] = array();

        if( isset($additional["limit"]) )
        {
            if( 0 != $additional["limit"] )
            {
                $params[ "Limit" ] = $additional["limit"];
                $title .= " (Limit=" . $params[ "Limit" ] . ")";
            }
        }

        if( isset($additional["offset"]) )
        {
            $params[ "Offset" ] = $additional["offset"];
            $title .= " (Offset=" . $params[ "Offset" ] . ")";
        }

        if( !eepValidate::validateContentNodeId( $subtreeNodeId ) )
            throw new Exception( "This is not an node id: [" .$subtreeNodeId. "]" );

        $allchildren = eZContentObjectTreeNode::subTreeByNodeID( $params, $subtreeNodeId );
        eep::displayNodeList( $allchildren, $title );
    }

    //--------------------------------------------------------------------------
    private function listExtensions()
    {
        $definedByFolder = array();

        $siteINI = eZINI::instance( "site.ini" );
        $extensionPath = "./" . $siteINI->variable( "ExtensionSettings", "ExtensionDirectory" );

        $hDir = opendir( $extensionPath );
        $file = readdir( $hDir );
        while( false != $file )
        {
            if( !in_array( $file , array(".", "..", ".svn") ))
            {
                if( is_dir( $extensionPath ."/". $file ) )
                    $definedByFolder[] = $file;
            }
            $file = readdir( $hDir );
        }

        $activeExtensions = $siteINI->variable( "ExtensionSettings", "ActiveExtensions" );
        $activeAccessExtensions = $siteINI->variable( "ExtensionSettings", "ActiveAccessExtensions" );

        $designINI = eZINI::instance( "design.ini" );
        $designExtensions = $designINI->variable( "ExtensionSettings", "DesignExtensions" );

        $moduleINI = eZINI::instance( "module.ini" );
        $moduleExtensions = $moduleINI->variable( "ModuleSettings", "ExtensionRepositories" );

        $results[] = array
        (
            "folders"
            , "ActiveExtensions"
            , "A.A.Extensions"
            , "design"
            , "modules"
        );

        // todo, there are more things that you could list here ... autoloads, cronjobs, workflow events ...
        // another thing that an extension might do is declare a siteaccess

        foreach( $definedByFolder as $folder )
        {
            $results[] = array
            (
                $folder
                , in_array( $folder, $activeExtensions ) ? $folder : ""
                , in_array( $folder, $activeAccessExtensions ) ? $folder : ""
                , in_array( $folder, $designExtensions ) ? $folder : ""
                , in_array( $folder, $moduleExtensions ) ? $folder : ""
            );
        }
        eep::printTable( $results, "list extensions" );
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

            case self::list_contentclasses:
                $this->listContentClasses();
                break;

            case self::list_attributes:
                $classIdentifier = $eepCache->readFromCache( eepCache::use_key_contentclass );
                if( $param1 )
                {
                    $classIdentifier = $param1;
                }
                AttributeFunctions::listAttributes( $classIdentifier );
                break;

            case self::list_all_attributes:
                $this->listAllAttributes();
                break;

            case self::list_children:
                $parentNodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $parentNodeId = $param1;
                }
                $this->listChildNodes( $parentNodeId, $additional );
                break;

            case self::list_siteaccesses:
                $this->listSiteAccesses();
                break;

            case self::list_allinifiles:
                $this->allinifiles();
                break;

            case self::list_subtree:
                $subtreeNodeId = $eepCache->readFromCache( eepCache::use_key_contentnode );
                if( $param1 )
                {
                    $subtreeNodeId = $param1;
                }
                $this->listSubtree( $subtreeNodeId, $additional );
                break;

            case self::list_extensions:
                $this->listExtensions();
                break;
        }
    }
}

//------------------------------------------------------------------------------
$operation = new list_commands();
if( !isset($argv[2]) )
{
    $argv[2] = "help";
}
$additional = eep::extractAdditionalParams( $argv );
$operation->run( $argv, $additional );
?>