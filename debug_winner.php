<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Peserta;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG WINNER DATA ===\n\n";

// Check total pemenang
echo "1. Total pemenang (raw query): " . DB::table('pesertas')->where('status_menang', 1)->count() . "\n";
echo "2. Total pemenang (model int 1): " . Peserta::where('status_menang', 1)->count() . "\n";
echo "3. Total pemenang (model bool true): " . Peserta::where('status_menang', true)->count() . "\n";
echo "4. Total pemenang (model bool false): " . Peserta::where('status_menang', false)->count() . "\n\n";

// Get all winners
echo "5. Daftar pemenang (raw query):\n";
$rawWinners = DB::table('pesertas')
    ->where('status_menang', 1)
    ->orderBy('waktu_menang', 'desc')
    ->limit(10)
    ->get();

foreach ($rawWinners as $winner) {
    echo "   - ID: {$winner->id}, No Rek: {$winner->no_rekening}, Nama: {$winner->nama}, Status: {$winner->status_menang} (type: " . gettype($winner->status_menang) . "), Waktu: {$winner->waktu_menang}\n";
}

echo "\n6. Daftar pemenang (model query):\n";
$modelWinners = Peserta::where('status_menang', 1)
    ->orderBy('waktu_menang', 'desc')
    ->limit(10)
    ->get();

foreach ($modelWinners as $winner) {
    echo "   - ID: {$winner->id}, No Rek: {$winner->no_rekening}, Nama: {$winner->nama}, Status: {$winner->status_menang} (type: " . gettype($winner->status_menang) . "), Waktu: {$winner->waktu_menang}\n";
}

echo "\n7. Check latest 5 records (all status):\n";
$latest = DB::table('pesertas')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

foreach ($latest as $record) {
    echo "   - ID: {$record->id}, No Rek: {$record->no_rekening}, Status: {$record->status_menang}, Waktu: {$record->waktu_menang}, Updated: {$record->updated_at}\n";
}

echo "\n=== END DEBUG ===\n";

