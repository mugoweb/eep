#!/usr/bin/php
<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * this switches the module it executes by "require_once"ing the file where
 * the module is defined; this is done by naming the folder after the module
 * and the main implementation file is "index.php"
 *
 * (for the eep PHP class, see /lib/eepHelpers.php :)
 *
 */

mb_internal_encoding( "UTF-8" );

$pathParts = pathinfo( __FILE__ );
$eepPath = $pathParts["dirname"];

if( file_exists( getenv("HOME")."/eepSetting.php" ) )
{
    require_once( getenv("HOME")."/eepSetting.php" );
}
elseif( file_exists( getenv("HOMEPATH")."/eepSetting.php" ) )
{
    require_once( getenv("HOMEPATH")."/eepSetting.php" );
}
else
{
    require_once( $eepPath . "/eepSetting.php" );
}
require_once( $eepPath . "/lib/eepHelpers.php" );
require_once( $eepPath . "/lib/eepCache.php" );
require_once( $eepPath . "/lib/AttributeFunctions.php" );
require_once( $eepPath . "/lib/eepLog.php" );
require_once( $eepPath . "/lib/eepValidate.php" );

$eepLogger = new eepLog( eepSetting::LogFolder, eepSetting::LogFile );

$eepCache = eepCache::getInstance();

$argModule = "help"; // default module in case no other is requested
if( isset($argv[1]) )
{
    // expand the module name into it's full name if using an alias
    $argv[1] = eep::expandAliases( $argv[1] );
    $argModule = $argv[1];
}

$argCommand = "";
if( isset($argv[2]) )
{
    // expand the function into it's full name if using an alias
    $argv[2] = eep::expandAliases( $argv[2] );
    $argCommand = $argv[2];
}

// make sure that the module is simple as a security precaution
$argModule = str_replace( array("/","\\",".",":"), "", $argModule );

// make sure that the module is one that is available
$availableModuleFolders = array();
$hDir = opendir( $eepPath . "/modules" );
$file = readdir( $hDir );
while( false != $file )
{
    if( !in_array( $file , array(".", "..", ".svn") ))
    {
        $wholePath = $eepPath . "/modules/" . $file;

        if( is_dir( $wholePath ) )
            $availableModuleFolders[] = $file;
    }
    $file = readdir( $hDir );
}
if( !in_array( $argModule, $availableModuleFolders ) )
{
    $msg = "Module is not supported. [" .$argModule. "]";
    $eepLogger->Report( $msg, "fatal" );
}

sort( $availableModuleFolders );

// this is an expensive way to accomplish a global, and provides no advantage
// unless you can eventually implement a lazy write protocol to the cache
$eepCache->writetoCache( eepCache::misc_key_availablemodules, $availableModuleFolders );

// this is a special, hopefully unique, case where the module affects the
// startup
if( "use"==$argModule && "ezroot"==$argCommand )
{
    $eepLogger->Report( "Reseting ezroot" );
}
else
{
    $eZPublishRootPath = $eepCache->readFromCache( eepCache::use_key_ezroot );
    if( isset( $eZPublishRootPath ) )
    {
        require $eZPublishRootPath.'/autoload.php';
        chdir( $eZPublishRootPath );
    }
    else
    {
        // currently unused
        //include_once( "ezc/Base/ezc_bootstrap.php" );
    }
}

// if we are operating in the context of an ez publish, equivalently, if we are
// operating on an ez instance, we have to loadup the ez context
if( class_exists ( "eZScript", true ) )
{
    // this isn't init'd in 4.6, or maybe it's something to do with cluster, anyway, initing it here prevents an ugly warning
    $GLOBALS['eZContentClassAttributeCacheListFull'] = null;
    $script = eZScript::instance
    (
        array
        (
            'description' => ( "eep (Ease eZ Publish) does some small tasks for you on the command line" )
            , 'use-session' => false
            , 'use-modules' => true
            , 'use-extensions' => true
        )
    );
    $script->initialize();

}

// do the main piece of work according to the index.php file in the requested folder
try
{
    require_once( $eepPath . "/modules/" . $argModule . "/index.php" );
}
catch( Exception $e )
{
    $msg = "An unhandled exception occured:\n";
    $msg .= $e->getMessage() . "\n";

    // make the exception legible
    $details = $e->getTrace();
    foreach( $details as $frame )
    {
        $msg .= " in " . $frame["file"];
        $msg .= " on line: " . $frame["line"] . "\n";
        $msg .= "   " .$frame["function"]. "( ";
        $space = "";
        foreach( $frame["args"] as $paramSet )
        {
            if( is_array($paramSet) )
            {
                foreach( $paramSet as $aParam )
                {
                    $msg .= $space . print_r( $aParam, true );
                    $space = " ";
                }
            }
            else
            {
                $msg .= $space . $paramSet;
                $space = " ";
            }
        }
        $msg .= " )\n";
    }
    // and output it
    $eepLogger->Report( $msg, "error" );
}

if( class_exists ( "eZScript", true ) )
{
    $script->setExitCode( 1 );
    $script->shutdown();
}
