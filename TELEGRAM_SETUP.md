# Setup Telegram Bot untuk Activity Monitoring

Sistem ini menggunakan Telegram Bot untuk mengirim notifikasi real-time setiap kali ada perubahan data peserta.

## Cara Setup Telegram Bot

### 1. Buat Telegram Bot

1. Buka Telegram dan cari **@BotFather**
2. Kirim pesan `/newbot`
3. Ikuti instruksi untuk memberikan nama bot (contoh: `BMT Lucky Draw Monitor`)
4. BotFather akan memberikan **Bot Token** (format: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)
5. Simpan token ini

### 2. Dapatkan Chat ID

**Opsi A: Menggunakan Bot untuk mendapatkan Chat ID**

1. Cari bot yang baru dibuat di Telegram
2. Kirim pesan apapun ke bot (contoh: `/start`)
3. Buka browser dan akses: `https://api.telegram.org/bot<BOT_TOKEN>/getUpdates`
   - Ganti `<BOT_TOKEN>` dengan token dari BotFather
4. Cari `"chat":{"id":123456789}` di response JSON
5. Angka tersebut adalah **Chat ID** Anda

**Opsi B: Menggunakan @userinfobot**

1. Cari **@userinfobot** di Telegram
2. Kirim pesan `/start`
3. Bot akan memberikan ID Anda

### 3. Konfigurasi di `.env`

Tambahkan konfigurasi berikut di file `.env`:

```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=123456789
```

**Catatan:**
- Ganti `123456789:ABCdefGHIjklMNOpqrsTUVwxyz` dengan Bot Token dari BotFather
- Ganti `123456789` dengan Chat ID Anda

### 4. Test Notifikasi

Setelah konfigurasi, coba:
1. Login ke admin panel
2. Tambah, edit, atau hapus peserta
3. Cek Telegram Anda - seharusnya menerima notifikasi

## Fitur Notifikasi

### ✅ Create (Tambah Peserta)
```
✅ **INFO:**
User: [Nama User]
Aksi: menambah peserta baru: *[Nama Peserta]* (No Rek: [No Rekening])
```

### ✏️ Update (Edit Peserta)
```
✏️ **UPDATE:**
User: [Nama User]
Aksi: mengubah data *[Nama Peserta]* (No Rek: [No Rekening])
```

### 🚨 Delete (Hapus Peserta) - CRITICAL ALERT
```
🚨 **DELETE ALERT:**
User: [Nama User]
Aksi: MENGHAPUS peserta *[Nama Peserta]* (No Rek: [No Rekening])
```

## Troubleshooting

### Notifikasi tidak terkirim?

1. **Cek konfigurasi `.env`**
   - Pastikan `TELEGRAM_BOT_TOKEN` dan `TELEGRAM_CHAT_ID` sudah diisi
   - Pastikan tidak ada spasi di awal/akhir nilai

2. **Cek Bot Token**
   - Pastikan token benar (format: `angka:teks`)
   - Pastikan bot masih aktif

3. **Cek Chat ID**
   - Pastikan Chat ID benar (format: angka)
   - Pastikan sudah mengirim pesan ke bot setidaknya sekali

4. **Cek Log Laravel**
   - Buka `storage/logs/laravel.log`
   - Cari pesan error terkait Telegram

### Telegram Down?

**Jangan khawatir!** Sistem dirancang agar jika Telegram down, aplikasi tetap berjalan normal. Notifikasi hanya akan gagal, tetapi:
- ✅ Data tetap tersimpan ke database
- ✅ Activity log tetap tercatat
- ✅ Aplikasi tidak error/macet

## Keamanan

- **Jangan commit `.env` ke Git** - Token dan Chat ID adalah informasi sensitif
- **Gunakan Bot Token yang berbeda** untuk development dan production
- **Batasi akses Chat ID** - hanya admin yang perlu menerima notifikasi

