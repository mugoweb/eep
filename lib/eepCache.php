<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/
/**
 * Get the instance via:
 *   $eepCache = eepCache::getInstance();
 *
 * This provides two functions; write and read to cache. Cache is stored in a
 * file specified in the user settings or defaults. The defaults probably say
 * /tmp/.eepdata and this goes away sometimes.
 */

class eepCache
{
    // these are keys for caching values, and also the corresponding part of the
    // command line; used like:
    // eep use ezroot <some path to an ez publish root folder>
    const use_key_ezroot                = "ezroot";
    const use_key_contentclass          = "contentclass";
    const use_key_contentnode           = "contentnode";
    const use_key_object                = "contentobject";
    const use_key_siteaccess            = "siteaccess";
    const use_key_attribute             = "attribute";

    const misc_key_availablemodules     = "availablemodules";
        
    private static $singleInstance;
    
    var $userDataCache = null;
    var $userDataCacheFile = null;

    //--------------------------------------------------------------------------
    function __construct()
    {
        global $eepLogger;
        
        $this->userDataCacheFile = eepSetting::DataCacheFile;
        
        if( file_exists( $this->userDataCacheFile ) )
        {
            $this->userDataCache = file_get_contents( $this->userDataCacheFile );
            $this->userDataCache = unserialize( $this->userDataCache );
        }
        else
        {
            $eepLogger->Report( "eepCache::__construct() no cache file exists [" . $this->userDataCacheFile . "]" );
        }
    }

    //--------------------------------------------------------------------------
    public function __clone()
    {
        throw new Exception( "eepCache:: Can not duplicate instances.");
    }

    //--------------------------------------------------------------------------
    public static function getInstance()
    {
        if( !isset( self::$singleInstance ) )
        {
            $class = __CLASS__;
            self::$singleInstance = new $class;
        }
        return self::$singleInstance;
    }

    //--------------------------------------------------------------------------
    public function writetoCache( $key, $value )
    {
        if( "" == $value )
        {
            unset( $this->userDataCache[ $key ] );
        }
        else
        {
            $this->userDataCache[ $key ] = $value;            
        }
        $fh = fopen( $this->userDataCacheFile, "w" );
        fwrite( $fh, serialize( $this->userDataCache ) );
        fclose( $fh );
    }
    
    //--------------------------------------------------------------------------
    public function readFromCache( $key )
    {
        if( isset($this->userDataCache[ $key ]) )
        {
            return $this->userDataCache[ $key ];
        }
        else
        {
            return null;
        }
    }

    //--------------------------------------------------------------------------
    public function getAll()
    {
        return $this->userDataCache;
    }

    //--------------------------------------------------------------------------
    public function cacheKeyIsSet( $key )
    {
        return isset( $this->userDataCache[ $key ] );
    }
}
?>