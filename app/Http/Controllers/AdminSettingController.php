<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminSettingController extends Controller
{
    public function downloadSqlBackup()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $fileName = 'backup_kilat_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path('app/' . $fileName);

        $command = "mysqldump --user={$dbUser} --password=\"{$dbPass}\" --host={$dbHost} {$dbName} > \"{$filePath}\"";

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filePath)) {
            return response()->download($filePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat backup SQL. Pastikan mysqldump tersedia pada server.');
    }
}
