<?php
/**
 * This is based on "eZLog class" - see <ez root>/lib/ezfile/classes/ezlog.php
 */

class eepLog
{
    var $logPath = "";
    var $logFile = "";
    
    var $maxLogRotateFiles = 3;
    var $maxLogFileSize = 204800; // 200*1024

    //--------------------------------------------------------------------------
    function eepLog( $path, $file )
    {
        $this->setPath( $path );
        $this->setFile( $file );
    }

    //--------------------------------------------------------------------------
    public function Report( $msg, $severity="normal" )
    {
        switch( $severity )
        {
            default:
            case "normal":
                $msg = "msg:: " . $msg;
                echo $msg . "\n";
                $this->write( $msg );
                break;

            case "error":
                $msg = "err:: " . $msg;
                echo $msg . "\n";
                $this->write( $msg );
                break;

            case "shy":
                $msg = "      " . $msg;
                echo $msg . "\n";
                $this->write( $msg );
                break;

            case "exception":
                $msg = "exception:: " . $msg;
                $this->write( $msg );
                throw new Exception( $msg );
                break;
            
            case "bell":
                $msg = "bel:: " . $msg;
                echo chr( 0x7 ) . $msg . "\n";
                $this->write( $msg );
                break;
            
            case "fatal":
                $msg = "fatal:: " . $msg;
                echo $msg . "\n";
                $this->write( $msg );
                die( "" );
                break;
        }
    }    

    //--------------------------------------------------------------------------
    public function setPath( $path )
    {
        $this->logPath = $path;
        if( "/" != $this->logPath[ strlen($this->logPath)-1 ] )
        {
            $this->logPath .= "/";
        }
    }

    //--------------------------------------------------------------------------
    public function setFile( $file )
    {
        $this->logFile = $file;
    }

    //--------------------------------------------------------------------------
    public function setMaxLogRotateFiles( $maxLogRotateFiles )
    {
        $this->maxLogRotateFiles = $maxLogRotateFiles;
    }

    //--------------------------------------------------------------------------
    public function setMaxLogFileSize( $maxLogFileSize )
    {
        $this->maxLogFileSize = $maxLogFileSize;
    }
    
    //--------------------------------------------------------------------------
    private function write( $message )
    {
        $fileName = $this->logPath . $this->logFile;
        $oldumask = @umask( 0 );

        $fileExisted = @file_exists( $fileName );
        if( $fileExisted and filesize( $fileName ) > $this->maxLogFileSize )
        {
            if( $this->rotateLog( $fileName ) )
            {
                $fileExisted = false;
            }
        }
        else if( !$fileExisted and !file_exists( $this->logPath ) )
        {
            // error, folder doesn't exist
        }

        $logFile = @fopen( $fileName, "a" );
        if( $logFile )
        {
            $time = strftime( "%b %d %Y %H:%M:%S", strtotime( "now" ) );
            $logMessage = "[ " . $time . " ] $message\n";
            @fwrite( $logFile, $logMessage );
            @fclose( $logFile );
        }
        else
        {
            // error, couldn't create the log file
        }
    }

    //--------------------------------------------------------------------------
    private function rotateLog( $fileName )
    {
        for( $i = $this->maxLogRotateFiles; $i > 0; --$i )
        {
            $logRotateName = $fileName . '.' . $i;
            if ( @file_exists( $logRotateName ) )
            {
                if( $i == $this->maxLogRotateFiles )
                {
                    @unlink( $logRotateName );
                }
                else
                {
                    $newLogRotateName = $fileName . '.' . ($i + 1);
                    eZFile::rename( $logRotateName, $newLogRotateName );
                }
            }
        }
        if ( @file_exists( $fileName ) )
        {
            $newLogRotateName = $fileName . '.' . 1;
            eZFile::rename( $fileName, $newLogRotateName );
            return true;
        }
        return false;
    }
}
?>