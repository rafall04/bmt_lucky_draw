<?php

namespace Database\Seeders;

use App\Models\Peserta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PesertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabangs = [
            'CABANG PUSAT',
            'CABANG SENORI',
            'CABANG TEMAYANG',
            'CABANG JATIROGO',
            'CABANG SINGGAHAN',
            'CABANG MERAKURAK',
            'CABANG SOKO',
            'CABANG PLUMPANG',
        ];

        $alamatPrefixes = [
            'Jl. Raya',
            'Jl. Ahmad Yani',
            'Jl. Sudirman',
            'Jl. Gatot Subroto',
            'Jl. Merdeka',
            'Jl. Diponegoro',
            'Jl. Imam Bonjol',
            'Jl. Kartini',
            'Jl. Pahlawan',
            'Jl. Veteran',
        ];

        $desaNames = [
            'TEMAYANG',
            'KALIGEDE',
            'JATIROGO',
            'SENORI',
            'SINGGAHAN',
            'MERAKURAK',
            'SOKO',
            'PLUMPANG',
            'BANCAR',
            'TUBAN',
        ];

        $pesertas = [];

        for ($i = 1; $i <= 3000; $i++) {
            $cabang = $cabangs[array_rand($cabangs)];
            $alamatPrefix = $alamatPrefixes[array_rand($alamatPrefixes)];
            $desa = $desaNames[array_rand($desaNames)];
            $noRumah = rand(1, 999);

            // Generate unique no_rekening (12 digits)
            $noRekening = str_pad($i, 12, '0', STR_PAD_LEFT);

            // Generate random Indonesian names
            $firstNames = ['Ahmad', 'Siti', 'Budi', 'Dewi', 'Eko', 'Fitri', 'Gunawan', 'Hani', 'Indra', 'Joko', 'Kartika', 'Lina', 'Muhammad', 'Nina', 'Omar', 'Putri', 'Rudi', 'Sari', 'Tono', 'Umi'];
            $lastNames = ['Santoso', 'Nurhaliza', 'Wijaya', 'Sari', 'Prasetyo', 'Rahayu', 'Kurniawan', 'Dewi', 'Setiawan', 'Lestari', 'Hidayat', 'Sari', 'Fauzi', 'Wati', 'Saputra', 'Indah', 'Purnomo', 'Kartika', 'Sutrisno', 'Rahmawati'];

            $nama = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

            $pesertas[] = [
                'no_rekening' => $noRekening,
                'nama' => $nama,
                'alamat' => $alamatPrefix . ' No. ' . $noRumah . ', ' . $desa,
                'cabang' => $cabang,
                'status_menang' => 0,
                'hadiah_didapat' => null,
                'waktu_menang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 500 for better performance
            if (count($pesertas) >= 500) {
                Peserta::insert($pesertas);
                $pesertas = [];
            }
        }

        // Insert remaining records
        if (!empty($pesertas)) {
            Peserta::insert($pesertas);
        }
    }
}

