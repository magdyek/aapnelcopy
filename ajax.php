<?php
// Enable full error reporting to debug SSH download 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent timeouts and memory limits for large file downloads
set_time_limit(0);
ini_set('memory_limit', '-1');

require __DIR__ . '/vendor/autoload.php';

use Migrator\AAPanelAPI;
use Migrator\Transfer;

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'test_new_connection':
            // Test New aaPanel
            $panelUrl = $_POST['aapanel_url'];
            $panelKey = $_POST['aapanel_key'];
            $api = new AAPanelAPI($panelUrl, $panelKey);
            $sites = $api->getSites();
            if (!isset($sites['data'])) {
                throw new \Exception('Invalid New aaPanel API credentials or cannot connect.');
            }
            echo json_encode(['status' => 'success', 'message' => 'Connected successfully!']);
            break;

        case 'test_old_connection':
            // Test Old aaPanel
            $oldPanelUrl = $_POST['old_aapanel_url'];
            $oldPanelKey = $_POST['old_aapanel_key'];
            $oldApi = new AAPanelAPI($oldPanelUrl, $oldPanelKey);
            $oldSites = $oldApi->getSites();
            if (!isset($oldSites['data'])) {
                throw new \Exception('Invalid Old aaPanel API credentials or cannot connect.');
            }
            $oldDbs = $oldApi->getDatabases();
            
            echo json_encode(['status' => 'success', 'message' => 'Connected successfully!', 'old_sites' => $oldSites['data'], 'old_dbs' => $oldDbs['data']]);
            break;

        case 'list_db_backups':
            $oldPanelUrl = $_POST['old_aapanel_url'] ?? '';
            $oldPanelKey = $_POST['old_aapanel_key'] ?? '';
            $oldDbId = $_POST['old_db_id'] ?? '';
            
            if (empty($oldDbId)) {
                throw new \Exception('Missing Database ID.');
            }
            
            $oldApi = new AAPanelAPI($oldPanelUrl, $oldPanelKey);
            $dbBackups = $oldApi->getDatabaseBackups($oldDbId);
            
            $backupsList = [];
            // Handle v1 (['data']) and v2 (['message']['data'])
            if (isset($dbBackups['message']['data']) && is_array($dbBackups['message']['data'])) {
                $backupsList = $dbBackups['message']['data'];
            } elseif (isset($dbBackups['data']) && is_array($dbBackups['data'])) {
                $backupsList = $dbBackups['data'];
            }

            echo json_encode(['status' => 'success', 'backups' => $backupsList]);
            break;

        case 'delete_db_backup':
            $oldPanelUrl = $_POST['old_aapanel_url'] ?? '';
            $oldPanelKey = $_POST['old_aapanel_key'] ?? '';
            $backupId = $_POST['backup_id'] ?? '';
            
            if (empty($backupId)) {
                throw new \Exception('Missing Backup ID.');
            }
            
            $oldApi = new AAPanelAPI($oldPanelUrl, $oldPanelKey);
            $res = $oldApi->deleteBackup($backupId);
            
            // Check status for v1 and v2
            if (isset($res['status']) && $res['status'] === false) {
                 throw new \Exception('Delete failed: ' . ($res['msg'] ?? 'Unknown error'));
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Backup deleted successfully.']);
            break;

        case 'create_site_db':
            $type = $_POST['migrate_type'] ?? 'both';
            $panelUrl = $_POST['aapanel_url'] ?? '';
            $panelKey = $_POST['aapanel_key'] ?? '';
            
            if (empty($panelUrl) || empty($panelKey)) {
                throw new \Exception('Missing New aaPanel URL or API Key for creation step.');
            }

            $api = new AAPanelAPI($panelUrl, $panelKey);

            $domain = $_POST['domain'] ?? '';
            $dbName = $_POST['new_db_name'] ?? '';
            $dbPass = $_POST['new_db_pass'] ?? '';

            if ($type === 'both' || $type === 'files') {
                if (empty($domain)) throw new \Exception('Domain is required to create a site.');
                // Create Site
                try {
                    $siteRes = $api->creatSite($domain);
                    if (isset($siteRes['status']) && $siteRes['status'] === false) {
                        if (strpos(strtolower($siteRes['msg']), 'exist') === false) {
                            throw new \Exception('Failed creating site: ' . $siteRes['msg']);
                        }
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Site Creation API Error: ' . $e->getMessage());
                }
            }

            if ($type === 'both' || $type === 'db') {
                if (empty($dbName) || empty($dbPass)) throw new \Exception('DB Name and Password are required.');
                // Create DB
                try {
                    $dbRes = $api->createDatabase($dbName, $dbPass);
                    if (isset($dbRes['status']) && $dbRes['status'] === false) {
                        if (strpos(strtolower($dbRes['msg']), 'exist') === false) {
                            throw new \Exception('Failed creating database: ' . $dbRes['msg']);
                        }
                    }
                } catch (\Exception $e) {
                    throw new \Exception('DB Creation API Error: ' . $e->getMessage());
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Site and/or Database created.']);
            break;

        case 'backup_remote':
            $type = $_POST['migrate_type'] ?? 'both';
            $oldPanelUrl = $_POST['old_aapanel_url'];
            $oldPanelKey = $_POST['old_aapanel_key'];
            $oldSiteId = $_POST['old_site_id'] ?? '';
            $oldDbId = $_POST['old_db_id'] ?? '';

            $oldApi = new AAPanelAPI($oldPanelUrl, $oldPanelKey);

            $latestSiteBackupFile = '';
            $latestDbBackupFile = '';

            if ($type === 'both' || $type === 'files') {
                // 1. Create Site Backup
                $siteRes = $oldApi->createSiteBackup($oldSiteId);
                if (!isset($siteRes['status']) || ($siteRes['status'] !== true && $siteRes['status'] !== 0 && $siteRes['status'] !== '0')) {
                    $raw = is_array($siteRes) ? json_encode($siteRes) : 'Unknown response';
                    throw new \Exception('Failed to trigger site backup: ' . ($siteRes['msg'] ?? $raw));
                }
                $siteBackups = $oldApi->getSiteBackups($oldSiteId);
                
                // Handle v1 (['data'][0]['filename']) and v2 (['message']['data'][0]['filename'])
                if (isset($siteBackups['message']['data'][0]['filename'])) {
                    $latestSiteBackupFile = $siteBackups['message']['data'][0]['filename'];
                } else {
                    $latestSiteBackupFile = $siteBackups['data'][0]['filename'] ?? '';
                }
                
                if (empty($latestSiteBackupFile)) {
                     $dbg = is_array($siteBackups) ? json_encode($siteBackups) : 'Unknown Array';
                     throw new \Exception('Site Backup created but filename not found. aaPanel returned: ' . $dbg);
                }
            }

            if ($type === 'both' || $type === 'db') {
                $existingDbBackup = $_POST['existing_db_backup'] ?? '';
                if (!empty($existingDbBackup)) {
                    $latestDbBackupFile = $existingDbBackup;
                } else {
                    $dbRes = $oldApi->createDatabaseBackup($oldDbId);
                    if (!isset($dbRes['status']) || ($dbRes['status'] !== true && $dbRes['status'] !== 0 && $dbRes['status'] !== '0')) {
                        $raw = is_array($dbRes) ? json_encode($dbRes) : 'Unknown response';
                        throw new \Exception('Failed to trigger DB backup. aaPanel says: ' . ($dbRes['msg'] ?? $raw));
                    }
                    $dbBackups = $oldApi->getDatabaseBackups($oldDbId);
                    
                    // Handle v1 (['data'][0]['filename']) and v2 (['message']['data'][0]['filename'])
                    if (isset($dbBackups['message']['data'][0]['filename'])) {
                        $latestDbBackupFile = $dbBackups['message']['data'][0]['filename'];
                    } else {
                        $latestDbBackupFile = $dbBackups['data'][0]['filename'] ?? '';
                    }

                    if (empty($latestDbBackupFile)) {
                         $dbg = is_array($dbBackups) ? json_encode($dbBackups) : 'Unknown Array';
                         throw new \Exception('DB Backup created but filename not found. aaPanel returned: ' . $dbg);
                    }
                }
            }

            echo json_encode([
                'status' => 'success', 
                'zip_file' => $latestSiteBackupFile,
                'sql_file' => $latestDbBackupFile,
                'message' => 'Backup created on remote aaPanel.'
            ]);
            break;

        case 'download_backup':
            $type = $_POST['migrate_type'] ?? 'both';
            
            $oldPanelUrl = $_POST['old_aapanel_url'] ?? '';
            $oldPanelKey = $_POST['old_aapanel_key'] ?? '';
            
            $remoteZip = $_POST['remote_zip'] ?? '';
            $remoteSql = $_POST['remote_sql'] ?? '';

            $localZip = __DIR__ . '/backup.tar.gz';
            // Determine local SQL filename based on remote extension
            $localSql = __DIR__ . '/backup.sql';
            if (strpos(strtolower($remoteSql), '.zip') !== false) {
                 $localSql .= '.zip';
            } elseif (strpos(strtolower($remoteSql), '.gz') !== false) {
                 $localSql .= '.gz';
            }
            
            if (empty($oldPanelUrl) || empty($oldPanelKey)) {
                 throw new \Exception("Missing Old aaPanel API credentials for download.");
            }

            $oldApi = new AAPanelAPI($oldPanelUrl, $oldPanelKey);
            
            // Download files via Old Server API
            if ($type === 'both' || $type === 'files') {
                if (!empty($remoteZip) && !$oldApi->downloadFile($remoteZip, $localZip)) {
                    throw new \Exception("Failed to download ZIP file via API.");
                }
            }
            if ($type === 'both' || $type === 'db') {
                if (!empty($remoteSql) && !$oldApi->downloadFile($remoteSql, $localSql)) {
                    throw new \Exception("Failed to download SQL file via API.");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Backup downloaded.', 'local_sql_file' => pathinfo($localSql, PATHINFO_BASENAME)]);
            break;

        case 'restore_local':
            $type = $_POST['migrate_type'] ?? 'both';
            $localZip = __DIR__ . '/backup.tar.gz';
            $localSqlBase = $_POST['local_sql_file'] ?? 'backup.sql';
            $localSqlPath = __DIR__ . '/' . $localSqlBase;
            
            $domain = $_POST['domain'] ?? '';
            $destDir = '/www/wwwroot/' . $domain;

            $dbName = $_POST['new_db_name'] ?? '';
            $dbPass = $_POST['new_db_pass'] ?? '';
            
            $panelUrl = $_POST['aapanel_url'] ?? '';
            $panelKey = $_POST['aapanel_key'] ?? '';

            if ($type === 'both' || $type === 'files') {
                // Unzip over existing dir
                if (!Transfer::extractLocalZip($localZip, $destDir)) {
                     throw new \Exception("Failed to extract ZIP to $destDir.");
                }
                @unlink($localZip);
            }

            if ($type === 'both' || $type === 'db') {
                if (empty($panelUrl) || empty($panelKey)) {
                    throw new \Exception('Missing New aaPanel URL or API Key for restore step.');
                }
                
                // Extract SQL if compressed before importing
                $finalSqlPath = __DIR__ . '/extracted_backup.sql';
                if (strpos($localSqlPath, '.zip') !== false) {
                     $zip = new ZipArchive;
                     if ($zip->open($localSqlPath) === TRUE) {
                          $sqlFileName = $zip->getNameIndex(0);
                          copy('zip://'.$localSqlPath.'#'.$sqlFileName, $finalSqlPath);
                          $zip->close();
                     } else {
                          throw new \Exception("Failed to open downloaded SQL Backup ZIP file.");
                     }
                } elseif (strpos($localSqlPath, '.gz') !== false) {
                     $buffer_size = 4096;
                     $out_file_name = $finalSqlPath;
                     $file = gzopen($localSqlPath, 'rb');
                     $out_file = fopen($out_file_name, 'wb');
                     while(!gzeof($file)) {
                          fwrite($out_file, gzread($file, $buffer_size));
                     }
                     fclose($out_file);
                     gzclose($file);
                } else {
                     copy($localSqlPath, $finalSqlPath);
                }

                $api = new AAPanelAPI($panelUrl, $panelKey);
                $importRes = $api->importDatabase($dbName, $finalSqlPath);
                
                if (isset($importRes['status']) && $importRes['status'] === false) {
                     $raw = is_array($importRes) ? json_encode($importRes) : 'Unknown Error';
                     throw new \Exception("Failed to import Database via API. aaPanel returned: " . ($importRes['msg'] ?? $raw));
                }
                
                @unlink($localSqlPath);
                @unlink($finalSqlPath);
            }

            echo json_encode(['status' => 'success', 'message' => 'Data restored successfully.']);
            break;

        case 'update_config':
            $domain = $_POST['domain'];
            $destDir = '/www/wwwroot/' . $domain;
            
            $dbName = $_POST['new_db_name'];
            $dbPass = $_POST['new_db_pass'];

            // Auto-detect .env or wp-config.php
            $updated = false;
            
            $envPath = $destDir . '/.env';
            if (file_exists($envPath)) {
                $content = file_get_contents($envPath);
                $content = preg_replace('/DB_DATABASE=.*$/m', 'DB_DATABASE=' . $dbName, $content);
                $content = preg_replace('/DB_USERNAME=.*$/m', 'DB_USERNAME=' . $dbName, $content);
                $content = preg_replace('/DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $dbPass, $content);
                file_put_contents($envPath, $content);
                $updated = true;
            }

            $wpConfigPath = $destDir . '/wp-config.php';
            if (file_exists($wpConfigPath)) {
                $content = file_get_contents($wpConfigPath);
                $content = preg_replace("/define\(\s*'DB_NAME',\s*'.*?'\s*\);/", "define('DB_NAME', '$dbName');", $content);
                $content = preg_replace("/define\(\s*'DB_USER',\s*'.*?'\s*\);/", "define('DB_USER', '$dbName');", $content);
                $content = preg_replace("/define\(\s*'DB_PASSWORD',\s*'.*?'\s*\);/", "define('DB_PASSWORD', '$dbPass');", $content);
                file_put_contents($wpConfigPath, $content);
                $updated = true;
            }

            echo json_encode(['status' => 'success', 'message' => $updated ? 'Configuration files updated.' : 'No config files needed updates.']);
            break;

        default:
            throw new \Exception('Unknown action: ' . $action);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
