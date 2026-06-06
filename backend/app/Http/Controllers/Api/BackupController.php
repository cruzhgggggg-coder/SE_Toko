<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    private const MAX_BACKUP_SIZE = 100 * 1024 * 1024; // 100MB max

    public function backup()
    {
        $dbPath = database_path('database.sqlite');

        if (!File::exists($dbPath)) {
            return response()->json(['message' => 'Database file not found.'], 404);
        }

        $filename = 'backup-' . date('Y-m-d-His') . '.sqlite';

        return Response::download($dbPath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(false);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:' . (self::MAX_BACKUP_SIZE / 1024),
        ]);

        $backupFile = $request->file('backup_file');

        // Validate file extension
        if ($backupFile->getClientOriginalExtension() !== 'sqlite') {
            return response()->json([
                'message' => 'Invalid file format. Please upload a .sqlite file.'
            ], 422);
        }

        // Verify the file is a valid SQLite database by reading its header
        $handle = fopen($backupFile->getRealPath(), 'rb');
        if (!$handle) {
            return response()->json([
                'message' => 'Unable to read uploaded file.'
            ], 422);
        }

        $header = fread($handle, 16);
        fclose($handle);

        if (substr($header, 0, 15) !== 'SQLite format 3') {
            return response()->json([
                'message' => 'The uploaded file is not a valid SQLite database.'
            ], 422);
        }

        $dbPath = database_path('database.sqlite');
        $preRestoreBackup = null;

        try {
            // Create timestamped backup of current database before restore
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $preRestoreBackup = $backupDir . '/pre-restore-' . date('Y-m-d-His') . '.sqlite';
            if (File::exists($dbPath)) {
                File::copy($dbPath, $preRestoreBackup);
            }

            // Replace current database
            File::copy($backupFile->getRealPath(), $dbPath);

            // Verify the new database is readable
            DB::reconnect();
            DB::getPdo()->query('SELECT 1 FROM users LIMIT 1');

            Log::info('Database restored successfully', [
                'user_id' => $request->user()->id,
                'backup_size' => $backupFile->getSize(),
            ]);

            return response()->json(['message' => 'Database restored successfully.']);

        } catch (\Exception $e) {
            // Attempt to restore from backup
            if ($preRestoreBackup && File::exists($preRestoreBackup)) {
                File::copy($preRestoreBackup, $dbPath);
            }

            Log::error('Database restore failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to restore database. Original database has been recovered.'
            ], 500);
        }
    }
}
