<?php

namespace App\Exports;

use App\Models\Peserta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaBackupExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $pesertas;

    public function __construct(Collection $pesertas)
    {
        $this->pesertas = $pesertas;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->pesertas;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'ID',
            'No Rekening',
            'Nama',
            'Alamat',
            'Cabang',
            'Status Menang',
            'Hadiah Didapat',
            'Waktu Menang',
            'Dibuat Pada',
            'Diperbarui Pada',
            'Dihapus Pada',
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
            $peserta->id,
            $peserta->no_rekening,
            $peserta->nama,
            $peserta->alamat,
            $peserta->cabang ?? '-',
            $peserta->status_menang ? 'Ya' : 'Tidak',
            $peserta->hadiah_didapat ?? '-',
            $peserta->waktu_menang ? format_wib($peserta->waktu_menang, 'd/m/Y H:i:s') . ' WIB' : '-',
            format_wib($peserta->created_at, 'd/m/Y H:i:s') . ' WIB',
            format_wib($peserta->updated_at, 'd/m/Y H:i:s') . ' WIB',
            $peserta->deleted_at ? format_wib($peserta->deleted_at, 'd/m/Y H:i:s') . ' WIB' : '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Data Peserta Undian';
    }
}

