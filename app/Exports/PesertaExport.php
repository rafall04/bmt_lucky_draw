<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Peserta::where('status_menang', 1)
            ->orderBy('waktu_menang', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'No Rekening',
            'Nama',
            'Alamat',
            'Cabang',
            'Hadiah',
            'Waktu Menang',
        ];
    }

    /**
     * @param Peserta $peserta
     * @return array
     */
    public function map($peserta): array
    {
        static $no = 1;
        return [
            $no++,
            $peserta->no_rekening,
            $peserta->nama,
            $peserta->alamat,
            $peserta->cabang ?? '-',
            $peserta->hadiah_didapat ?? '-',
            $peserta->waktu_menang ? $peserta->waktu_menang->format('d/m/Y H:i:s') : '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
