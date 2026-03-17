<?php

namespace Migrator;

use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;

class Transfer
{
    private $ssh;
    private $sftp;
    private $host;
    private $port;
    private $user;

    public function __construct($host, $port, $user, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;

        $this->ssh = new SSH2($host, $port);
        if (!$this->ssh->login($user, $password)) {
            throw new \Exception('Login Failed to remote server.');
        }

        $this->sftp = new SFTP($host, $port);
        if (!$this->sftp->login($user, $password)) {
            throw new \Exception('SFTP Login Failed to remote server.');
        }
    }

    /**
     * Zip a remote directory via SSH
     */
    public function zipRemoteDirectory($remoteDir, $zipFileName)
    {
        // Change dir and compress
        $command = "cd " . escapeshellarg($remoteDir) . " && tar -czf " . escapeshellarg($zipFileName) . " .";
        $output = $this->ssh->exec($command);
        return $output;
    }

    /**
     * Download a remote file to local via SFTP
     */
    public function downloadFile($remoteFilePath, $localFilePath)
    {
        return $this->sftp->get($remoteFilePath, $localFilePath);
    }

    /**
     * Dump Remote Database
     */
    public function dumpRemoteDatabase($dbUser, $dbPass, $dbName, $dumpFileName)
    {
        $command = "mysqldump -u " . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPass) . " " . escapeshellarg($dbName) . " > " . escapeshellarg($dumpFileName);
        $this->ssh->exec($command);
        
        // Wait a little bit for the file to be ready
        sleep(2);
    }
    
    /**
     * Delete remote file
     */
    public function deleteRemoteFile($remoteFilePath)
    {
         $this->sftp->delete($remoteFilePath);
    }

    /**
     * Import Dump to Local DB locally
     */
    public static function importLocalDatabase($dbUser, $dbPass, $dbName, $dumpFilePath)
    {
        // Using exec to run mysql locally on the new server
        $command = "mysql -u " . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPass) . " " . escapeshellarg($dbName) . " < " . escapeshellarg($dumpFilePath);
        exec($command, $output, $result_code);
        return $result_code === 0;
    }
    
    /**
     * Extract Local Zip locally
     */
    public static function extractLocalZip($zipFilePath, $destDir)
    {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $command = "tar -xzf " . escapeshellarg($zipFilePath) . " -C " . escapeshellarg($destDir);
        exec($command, $output, $result_code);
        return $result_code === 0;
    }
}
