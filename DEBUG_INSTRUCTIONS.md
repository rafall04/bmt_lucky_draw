# Debugging Instructions untuk Masalah Pemenang Tidak Tersimpan

## Langkah-langkah Debugging

### 1. Cek Log File
Setelah melakukan pickWinner, cek log file:
```bash
tail -f storage/logs/laravel.log
```

Atau buka file `storage/logs/laravel.log` dan cari log dengan keyword:
- `pickWinner:`
- `winners:`

### 2. Test Manual dengan Script Debug
Jalankan script debug untuk melihat data di database:
```bash
php debug_winner.php
```

Script ini akan menampilkan:
- Total pemenang dengan berbagai query method
- Daftar pemenang terbaru
- Latest 5 records untuk melihat update terbaru

### 3. Test Query Langsung
Jalankan query langsung di tinker:
```bash
php artisan tinker
```

Kemudian jalankan:
```php
// Cek total pemenang
DB::table('pesertas')->where('status_menang', 1)->count();
Peserta::where('status_menang', 1)->count();

// Cek pemenang terbaru
Peserta::where('status_menang', 1)->orderBy('waktu_menang', 'desc')->limit(5)->get();
```

### 4. Cek Browser Console
Buka browser console (F12) dan cek:
- Apakah ada error JavaScript?
- Apakah event `winner-selected` ter-dispatch?
- Apakah ada error network?

### 5. Cek Network Tab
Di browser, buka Network tab dan cek:
- Apakah request ke `/admin/winners` berhasil?
- Apakah response JSON/HTML benar?
- Apakah ada error 500 atau lainnya?

## Informasi yang Dicatat di Log

### pickWinner Method
Log akan mencatat:
1. **Memulai update peserta**: ID, no_rekening, nama, status sebelum update
2. **Hasil update**: Jumlah row yang di-update
3. **Transaction committed**: Konfirmasi commit
4. **Data setelah commit (raw query)**: Status, waktu_menang, no_rekening, nama
5. **Data dari model setelah reload**: Status dengan type, waktu_menang, dll
6. **Verifikasi dengan model query**: Hasil verifikasi dengan boolean true
7. **Verifikasi dengan integer 1**: Hasil verifikasi dengan integer 1

### winners Method
Log akan mencatat:
1. **Method dipanggil**: Request parameters
2. **Query counts**: Count dengan berbagai method query
3. **Hasil query**: Total winners, current page, first item details

## Troubleshooting

### Jika data tidak tersimpan:
1. Cek log untuk melihat apakah update berhasil (rows_updated > 0)
2. Cek apakah transaction commit berhasil
3. Cek apakah verifikasi gagal (ada error di log)
4. Cek apakah ada exception yang tidak tertangkap

### Jika data tersimpan tapi tidak muncul di daftar:
1. Cek log `winners:` untuk melihat query counts
2. Cek apakah query menggunakan filter yang salah
3. Cek apakah ada masalah dengan pagination
4. Cek apakah view benar-benar menggunakan data dari controller

### Jika ada error di log:
1. Baca error message dengan detail
2. Cek stack trace untuk melihat di mana error terjadi
3. Cek apakah ada masalah dengan database connection
4. Cek apakah ada masalah dengan model casting

## Expected Behavior

Setelah pickWinner dipanggil:
1. Log harus menunjukkan `rows_updated: 1`
2. Log harus menunjukkan `Transaction committed`
3. Log harus menunjukkan data tersimpan dengan benar
4. Log harus menunjukkan verifikasi berhasil
5. Data harus muncul di halaman `/admin/winners`

