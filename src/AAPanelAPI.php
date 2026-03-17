<?php

namespace Migrator;

class AAPanelAPI
{
    private $panelUrl;
    private $apiKey;

    public function __construct($panelUrl, $apiKey)
    {
        // Remove trailing slash if exists
        $panelUrl = rtrim($panelUrl, '/');
        
        // Remove the security entrance (e.g. /557a1d48) from the end of the URL if user pasted it by mistake
        // We only want the scheme://host:port part for API calls.
        $parsedUrl = parse_url($panelUrl);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : 'http://';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : $panelUrl;
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        
        $this->panelUrl = $scheme . $host . $port;
        $this->apiKey = $apiKey;
    }

    /**
     * Send POST request to aaPanel API with optional fallback URI
     */
    private function request($uri, $data = [], $fallbackUri = null)
    {
        $url = $this->panelUrl . '/' . ltrim($uri, '/');
        
        $time = time();
        $token = md5($time . '' . md5($this->apiKey));
        
        $requestData = array_merge([
            'request_time' => $time,
            'request_token' => $token,
        ], $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        // If 404 Nginx and we have a fallback, try it
        if ($httpCode == 404 && $fallbackUri !== null && strpos(strtolower($response), '404 not found') !== false) {
             return $this->request($fallbackUri, $data, null);
        }
        
        $result = json_decode($response, true);
        
        if ($result === null) {
            // API didn't return valid JSON. This usually indicates an error like IP not whitelisted or bad URL
            $errMsg = strip_tags($response);
            if (empty(trim($errMsg))) {
                $errMsg = "Empty response received. Ensure the URL is correct and your IP is added to the API IP Whitelist in aaPanel.";
            } else if (strpos(strtolower($errMsg), 'ip') !== false) {
                 $errMsg = "IP Whitelist Error: Your IP is not authorized in aaPanel API settings. Response: " . $errMsg;
            } else {
                 $errMsg = "Raw API Response: " . substr($response, 0, 200); // include raw response for debug
            }
            throw new \Exception('API Response Error (Not JSON): ' . $errMsg);
        }

        // aaPanel returns {"status": false, "msg": "..."} on logical errors
        if (isset($result['status']) && $result['status'] === false && isset($result['msg'])) {
             // In some endpoints we handle status in the caller, but if it's a global error we might throw.
             // For getSites(), it returns an array without 'status' on success, or status=false on fail
             if (strpos($uri, 'data?action=getData') !== false) {
                  throw new \Exception('API Error: ' . $result['msg']);
             }
        }

        return $result;
    }

    /**
     * Get list of sites
     */
    public function getSites()
    {
        return $this->request('data?action=getData&table=sites');
    }

    /**
     * Add a new site
     */
    public function creatSite($domain, $path = '')
    {
        $domainJson = json_encode(['domain' => $domain, 'domainlist' => [], 'count' => 0]);
        
        $data = [
            'webname' => $domainJson,
            'path' => empty($path) ? '/www/wwwroot/' . $domain : $path,
            'type' => 'PHP',
            'version' => '00', // auto select php version or skip 
            'port' => '80',
            'ps' => 'Created via Smart Migrator'
        ];
        
        return $this->request('site?action=AddSite', $data);
    }

    /**
     * Add a database
     */
    public function createDatabase($name, $password, $username = '')
    {
        if (empty($username)) {
            $username = $name;
        }

        $data = [
            'name' => $name,
            'codeing' => 'utf8mb4',
            'db_user' => $username,
            'password' => $password,
            'dataAccess' => '127.0.0.1',
            'sid' => '0',
            'active' => 'false',
            'address' => '127.0.0.1',
            'ps' => 'Created via Smart Migrator',
            'ssl' => '',
            'dtype' => 'MySQL'
        ];

        return $this->request('v2/database?action=AddDatabase', $data, 'database?action=AddDatabase');
    }

    /**
     * Import a Database SQL file
     */
    public function importDatabase($dbName, $sqlFilePath)
    {
        $data = [
            'name' => $dbName,
            'file' => $sqlFilePath
        ];
        return $this->request('v2/database?action=InputSql', $data, 'database?action=InputSql');
    }

    /**
     * Check Database list to get DB ID for backup
     */
    public function getDatabases()
    {
         return $this->request('data?action=getData&table=databases');
    }

    /**
     * Create Site Backup
     */
    public function createSiteBackup($siteId)
    {
        $data = [
            'id' => $siteId
        ];
        return $this->request('site?action=ToBackup', $data);
    }

    /**
     * Create Database Backup
     */
    public function createDatabaseBackup($dbId)
    {
         $data = [
            'id' => $dbId
        ];
        return $this->request('v2/database?action=ToBackup', $data, 'database?action=ToBackup');
    }

    /**
     * Get list of physical backups for site
     */
    public function getSiteBackups($siteId)
    {
        $data = [
            'p' => '1',
            'limit' => '10',
            'search' => $siteId,
            'table' => 'backup',
            'type' => '0'
        ];
        return $this->request('v2/data?action=getData', $data, 'data?action=getData&table=backup&search='.$siteId.'&type=0');
    }

    /**
     * Get list of physical backups for DB
     */
    public function getDatabaseBackups($dbId)
    {
         $data = [
            'p' => '1',
            'limit' => '10',
            'search' => $dbId,
            'table' => 'backup',
            'type' => '1'
        ];
        return $this->request('v2/data?action=getData', $data, 'data?action=getData&table=backup&search='.$dbId.'&type=1');
    }

    /**
     * Delete a physical backup file
     */
    public function deleteBackup($backupId)
    {
         $data = [
            'id' => $backupId
        ];
        return $this->request('data?action=DelBackup', $data, 'v2/data?action=DelBackup');
    }

    /**
     * Download a file from aaPanel directly via API
     */
    public function downloadFile($remoteFilePath, $localFilePath)
    {
        $uri = 'download?filename=' . urlencode($remoteFilePath);
        $url = $this->panelUrl . '/' . ltrim($uri, '/');
        
        $time = time();
        $token = md5($time . '' . md5($this->apiKey));
        
        // Append auth params to URL for GET request
        $url .= '&request_time=' . $time . '&request_token=' . $token;

        $fp = fopen($localFilePath, 'w+');
        if ($fp === false) {
             throw new \Exception("Cannot open local file for writing: $localFilePath");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // No timeout for large files
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            @unlink($localFilePath);
            throw new \Exception('cURL error during download: ' . $err);
        }
        
        curl_close($ch);
        fclose($fp);
        
        if ($httpCode !== 200) {
             @unlink($localFilePath);
             throw new \Exception("Failed to download file. HTTP Status Code: $httpCode (URL: $url)");
        }
        
        return true;
    }
}
