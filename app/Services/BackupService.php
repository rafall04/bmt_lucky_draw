<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupService
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a full database backup.
     *
     * @param int $userId
     * @param string|null $description
     * @return Backup
     * @throws \Exception
     */
    public function createBackup(int $userId, ?string $description = null, string $type = 'full'): Backup
    {
        $backup = Backup::create([
            'user_id' => $userId,
            'filename' => $this->generateFilename($type),
            'file_path' => '',
            'type' => $type,
            'description' => $description,
            'status' => 'pending',
        ]);

        try {
            $connection = DB::connection();
            $config = $connection->getConfig();
            
            $filename = $backup->filename;
            $filePath = $this->backupPath . '/' . $filename;
            $content = $this->generateSqlDump($config);
            File::put($filePath, $content);
            
            $backup->update([
                'file_path' => $filePath,
                'file_size' => (string) File::size($filePath),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            log_activity('create', "Membuat backup database: {$filename}");

            return $backup;
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore database from backup.
     *
     * @param Backup $backup
     * @return bool
     * @throws \Exception
     */
    public function restoreBackup(Backup $backup): bool
    {
        if (!$backup->fileExists()) {
            throw new \Exception('Backup file tidak ditemukan.');
        }

        try {
            DB::beginTransaction();

            if ($backup->type === 'peserta') {
                $this->restorePesertaBackup($backup);
            } else {
                $this->restoreFullBackup($backup);
            }
            
            DB::commit();

            $backupType = $backup->type === 'peserta' ? 'data peserta' : 'database';
            log_activity('restore', "Restore {$backupType} dari backup: {$backup->filename}");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore full database backup.
     *
     * @param Backup $backup
     * @return void
     */
    protected function restoreFullBackup(Backup $backup): void
    {
        $sql = File::get($backup->file_path);
        $statements = $this->splitSqlStatements($sql);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !$this->isComment($statement)) {
                try {
                    DB::statement($statement);
                } catch (\Exception $e) {
                    Log::warning('SQL statement failed during restore', [
                        'statement' => substr($statement, 0, 100),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Restore peserta backup (only pesertas table).
     *
     * @param Backup $backup
     * @return void
     */
    protected function restorePesertaBackup(Backup $backup): void
    {
        $sql = File::get($backup->file_path);
        $statements = $this->splitSqlStatements($sql);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !$this->isComment($statement)) {
                if (stripos($statement, 'pesertas') !== false) {
                    try {
                        DB::statement($statement);
                    } catch (\Exception $e) {
                        Log::warning('SQL statement failed during peserta restore', [
                            'statement' => substr($statement, 0, 100),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Delete backup file and record.
     *
     * @param Backup $backup
     * @return bool
     */
    public function deleteBackup(Backup $backup): bool
    {
        try {
            if ($backup->fileExists()) {
                File::delete($backup->file_path);
            }

            $backup->delete();
            log_activity('delete', "Menghapus backup: {$backup->filename}");

            return true;
        } catch (\Exception $e) {
            Log::error('Delete backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Download backup file.
     *
     * @param Backup $backup
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function downloadBackup(Backup $backup)
    {
        if (!$backup->fileExists()) {
            throw new \Exception('Backup file tidak ditemukan.');
        }

        return response()->download($backup->file_path, $backup->filename);
    }

    /**
     * Clean up old backups (older than specified days).
     *
     * @param int $days
     * @return int Number of backups deleted
     */
    public function cleanupOldBackups(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
        
        $deleted = 0;
        foreach ($oldBackups as $backup) {
            if ($this->deleteBackup($backup)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Generate SQL dump from database configuration.
     *
     * @param array $config
     * @return string
     */
    protected function generateSqlDump(array $config): string
    {
        $sql = "-- BMT Lucky Draw Database Backup\n";
        $sql .= "-- Generated: " . now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . " WIB\n";
        $sql .= "-- Database: {$config['database']}\n";
        $sql .= "-- Backup Type: FULL (All tables except migrations)\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

        $tables = DB::select('SHOW TABLES');
        $databaseName = $config['database'];
        $tableKey = "Tables_in_{$databaseName}";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            if ($tableName === 'migrations') {
                continue;
            }

            $sql .= "\n-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$tableName}`\n";
                $sql .= "LOCK TABLES `{$tableName}` WRITE;\n";
                $sql .= "/*!40000 ALTER TABLE `{$tableName}` DISABLE KEYS */;\n";

                $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
                
                foreach ($rows as $row) {
                    $values = [];
                    $rowArray = (array) $row;
                    
                    foreach ($columns as $column) {
                        $value = $rowArray[$column] ?? null;
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    
                    $columnList = '`' . implode('`,`', $columns) . '`';
                    $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES (" . implode(',', $values) . ");\n";
                }

                $sql .= "/*!40000 ALTER TABLE `{$tableName}` ENABLE KEYS */;\n";
                $sql .= "UNLOCK TABLES;\n\n";
            }
        }

        $sql .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

        return $sql;
    }

    /**
     * Create backup for peserta data only.
     *
     * @param int $userId
     * @param string|null $description
     * @param string $format
     * @return Backup
     * @throws \Exception
     */
    public function createPesertaBackup(int $userId, ?string $description = null, string $format = 'sql'): Backup
    {
        $backup = Backup::create([
            'user_id' => $userId,
            'filename' => $this->generateFilename('peserta', $format),
            'file_path' => '',
            'type' => 'peserta',
            'description' => $description,
            'status' => 'pending',
        ]);

        try {
            // Generate backup file path
            $filename = $backup->filename;
            $filePath = $this->backupPath . '/' . $filename;
            
            if ($format === 'excel') {
                $this->generatePesertaExcelBackup($filePath);
                $backup->update(['file_path' => $filePath]);
            } else {
                $content = $this->generatePesertaBackup();
                File::put($filePath, $content);
            }
            
            $backup->update([
                'file_path' => $filePath,
                'file_size' => (string) File::size($filePath),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            log_activity('create', "Membuat backup peserta: {$filename}");

            return $backup;
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Peserta backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate unique filename for backup.
     *
     * @param string $type
     * @param string $format
     * @return string
     */
    protected function generateFilename(string $type = 'full', string $format = 'sql'): string
    {
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d_His');
        $random = Str::random(6);
        
        if ($type === 'peserta') {
            return "backup_peserta_{$timestamp}_{$random}.{$format}";
        }
        
        return "backup_{$timestamp}_{$random}.sql";
    }

    /**
     * Split SQL statements.
     *
     * @param string $sql
     * @return array
     */
    protected function splitSqlStatements(string $sql): array
    {
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $statements = explode(';', $sql);
        
        return array_filter(array_map('trim', $statements));
    }

    /**
     * Check if line is a comment.
     *
     * @param string $line
     * @return bool
     */
    protected function isComment(string $line): bool
    {
        $line = trim($line);
        return empty($line) || 
               strpos($line, '--') === 0 || 
               strpos($line, '/*') === 0 ||
               strpos($line, 'SET') === 0 ||
               strpos($line, 'LOCK') === 0 ||
               strpos($line, 'UNLOCK') === 0 ||
               strpos($line, '/*!') === 0;
    }

    /**
     * Generate SQL backup for peserta table only.
     *
     * @return string
     */
    protected function generatePesertaBackup(): string
    {
        $sql = "-- BMT Lucky Draw - Backup Data Peserta Undian\n";
        $sql .= "-- Generated: " . now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . " WIB\n";
        $sql .= "-- Backup Type: PESERTA (Data Peserta Undian Only)\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

        $tableName = 'pesertas';

        $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
        $sql .= "-- Table structure for table `{$tableName}`\n";
        $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

        $pesertas = \App\Models\Peserta::withTrashed()->orderBy('id')->get();
        
        if ($pesertas->count() > 0) {
            $sql .= "-- Dumping data for table `{$tableName}`\n";
            $sql .= "LOCK TABLES `{$tableName}` WRITE;\n";
            $sql .= "/*!40000 ALTER TABLE `{$tableName}` DISABLE KEYS */;\n";

            $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
            
            foreach ($pesertas as $peserta) {
                $values = [];
                
                foreach ($columns as $column) {
                    $value = $peserta->getAttribute($column);
                    if ($value === null) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($value) && !is_string($value)) {
                        $values[] = $value;
                    } elseif (is_bool($value)) {
                        $values[] = $value ? 1 : 0;
                    } elseif ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
                        $values[] = "'" . $value->format('Y-m-d H:i:s') . "'";
                    } else {
                        $values[] = "'" . addslashes((string) $value) . "'";
                    }
                }
                
                $columnList = '`' . implode('`,`', $columns) . '`';
                $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES (" . implode(',', $values) . ");\n";
            }

            $sql .= "/*!40000 ALTER TABLE `{$tableName}` ENABLE KEYS */;\n";
            $sql .= "UNLOCK TABLES;\n\n";
        }

        $totalPeserta = \App\Models\Peserta::withTrashed()->count();
        $totalPemenang = \App\Models\Peserta::withTrashed()->where('status_menang', 1)->count();
        $totalBelumMenang = \App\Models\Peserta::withTrashed()->where('status_menang', 0)->count();
        $totalDeleted = \App\Models\Peserta::onlyTrashed()->count();

        $sql .= "-- Statistics:\n";
        $sql .= "-- Total Peserta: {$totalPeserta}\n";
        $sql .= "-- Total Pemenang: {$totalPemenang}\n";
        $sql .= "-- Total Belum Menang: {$totalBelumMenang}\n";
        $sql .= "-- Total Dihapus (Soft Delete): {$totalDeleted}\n\n";

        $sql .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

        return $sql;
    }

    /**
     * Generate Excel backup for peserta data.
     *
     * @param string $filePath
     * @return void
     */
    protected function generatePesertaExcelBackup(string $filePath): void
    {
        $pesertas = \App\Models\Peserta::withTrashed()->orderBy('id')->get();
        
        // Use Excel facade to store file
        \Maatwebsite\Excel\Facades\Excel::store(
            new \App\Exports\PesertaBackupExport($pesertas),
            'backups/' . basename($filePath),
            'local',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}

