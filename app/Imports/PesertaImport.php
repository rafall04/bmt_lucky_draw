<?php

namespace App\Imports;

use App\Models\Peserta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PesertaImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($row['no_rekening'])) {
            return null;
        }

        $existing = Peserta::where('no_rekening', $row['no_rekening'])->first();
        if ($existing) {
            return null;
        }

        return new Peserta([
            'no_rekening' => $row['no_rekening'],
            'nama' => $row['nama'] ?? '',
            'alamat' => $row['alamat'] ?? '',
            'cabang' => $row['cabang'] ?? null,
            'status_menang' => 0,
        ]);
    }
}

