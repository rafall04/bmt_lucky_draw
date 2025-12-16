# Excel Template Format

Untuk mengimport data peserta, gunakan format Excel berikut:

## Format File
- Ekstensi: `.xlsx` atau `.xls`
- Baris pertama harus berisi header (akan diabaikan oleh sistem)

## Kolom yang Diperlukan

| no_rekening | nama | alamat | cabang |
|-------------|------|--------|--------|
| REK001      | Ahmad Fauzi | Jl. Merdeka No. 123 | Cabang Pusat |
| REK002      | Siti Nurhaliza | Jl. Sudirman No. 45 | Cabang Jakarta |
| REK003      | Budi Santoso | Jl. Gatot Subroto No. 67 | Cabang Bandung |

## Catatan Penting

1. **no_rekening** (WAJIB): Harus unik, tidak boleh duplikat
2. **nama** (WAJIB): Nama lengkap peserta
3. **alamat** (WAJIB): Alamat lengkap peserta
4. **cabang** (OPSIONAL): Nama cabang BMT

## Contoh Data

```
no_rekening | nama           | alamat                    | cabang
REK000001   | Ahmad Fauzi    | Jl. Merdeka No. 123       | Cabang Pusat
REK000002   | Siti Nurhaliza | Jl. Sudirman No. 45       | Cabang Jakarta
REK000003   | Budi Santoso   | Jl. Gatot Subroto No. 67  | Cabang Bandung
```

## Validasi

- Baris dengan `no_rekening` kosong akan diabaikan
- Baris dengan `no_rekening` duplikat akan diabaikan
- Data yang sudah ada di database (berdasarkan `no_rekening`) akan diabaikan

