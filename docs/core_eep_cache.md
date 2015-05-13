# Core - eepCache
> A collection of methods to read, write and check the eep cache.

`Note:` The eep cache is stored in a file specified in the user settings `eepSetting::DataCacheFile`.

- [getInstance](#getinstance)
- [writetoCache](#writetocache)
- [readFromCache](#readfromcache)
- [getAll](#getall)
- [cacheKeyIsSet](#cachekeyisset)

## getInstance
> Returns an eepCache instance.

## writetoCache
> Writes (adds/updates) a value to the eepCache using the key provided. An empty value removes the cache entry.

*Parameters:*
- `$key` String
- `$value` String


## readFromCache
> Reads a value from the eepCache using the key provided.

*Parameters:*
- `$key` String


## getAll
> Returns the entire eep cache.

## cacheKeyIsSet
> Returns if the given key exists in the eep cache.

*Parameters:*
- `$key` String

