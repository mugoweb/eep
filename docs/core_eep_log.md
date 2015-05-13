# Core - eepLog
> A logging class based on "eZLog class" - see <ez root>/lib/ezfile/classes/ezlog.php.

`Note:` eepLog methods are not static.

`Public`
- [eepLog](#eepLog)
- [Report](#Report)
- [setPath](#setPath)
- [setFile](#setFile)
- [setMaxLogRotateFiles](#setMaxLogRotateFiles)
- [setMaxLogFileSize](#setMaxLogFileSize)

`Private`
- [write](#write)
- [rotateLog](#rotateLog)

# eepLog
> Constructor method

*Parameters:*
- `$path` String
- `$file` String

```php
$eepLogger = new eepLog( eepSetting::LogFolder, eepSetting::LogFile );
```

# Report
> Outputs log message with severity.

*Parameters:*
- `$msg` String
- `$severity` String; (normal|error|shy|exception|bell|fatal); Default = normal

`Note:` 
- severity `exception` will throw an exception
- severity `fatal` will die


# setPath
> Sets the log file path.

*Parameters:*
- `$path` String


# setFile
> Sets the log file name.

*Parameters:*
- `$file` String


# setMaxLogRotateFiles
> Set the maximum amount of rotation log files before deletion occurs.

*Parameters:*
- `$maxLogRotateFiles` Integer


# setMaxLogFileSize
> Set the maximum log file size.

*Parameters:*
- `setMaxLogFileSize` Integer; (in bytes)


# write
> `Private` Writes the log message to the log file. Triggers log rotation as required.

*Parameters:*
- `$message` String


# rotateLog
> `Private` Handles log file rotation and cleanup.

*Parameters:*
- `$fileName` String

*Return:*
- Boolean

