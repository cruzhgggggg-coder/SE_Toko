<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function backup()
    {
        $dbPath = database_path('database.sqlite');
        
        if (!File::exists($dbPath)) {
            return response()->json(['message' => 'Database file not found.'], 404);
        }

        return Response::download($dbPath, 'backup-' . date('Y-m-d-H-i-s') . '.sqlite');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $dbPath = database_path('database.sqlite');
        $backupFile = $request->file('backup_file');

        // Basic validation that it's a sqlite file (or at least named so)
        if ($backupFile->getClientOriginalExtension() !== 'sqlite') {
            return response()->json(['message' => 'Invalid file format. Please upload a .sqlite file.'], 400);
        }

        try {
            // Backup current database just in case
            if (File::exists($dbPath)) {
                File::copy($dbPath, $dbPath . '.bak');
            }

            // Replace current database
            File::copy($backupFile->getRealPath(), $dbPath);

            return response()->json(['message' => 'Database restored successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to restore database: ' . $e->getMessage()], 500);
        }
    }
}
