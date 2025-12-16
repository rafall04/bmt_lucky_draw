@php
    use Illuminate\Support\Str;
@endphp
<x-admin-layout title="Buat Backup Baru">
    <div class="max-w-2xl mx-auto" x-data="{ type: '{{ old('type', 'full') }}', format: '{{ old('format', 'sql') }}' }">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Buat Backup Baru</h1>
            <p class="text-gray-600 mt-2">Pilih tipe backup yang ingin dibuat</p>
        </div>

        <!-- Warning Card -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-yellow-900 mb-1">Perhatian</h3>
                    <p class="text-sm text-yellow-800">
                        Proses backup mungkin memakan waktu beberapa saat tergantung ukuran database. 
                        Jangan tutup halaman ini sampai proses selesai.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form action="{{ route('admin.backups.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Backup Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Tipe Backup <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="type === 'full' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                   @click="type = 'full'">
                                <input type="radio" 
                                       name="type" 
                                       value="full" 
                                       x-model="type"
                                       class="mt-1 mr-3 text-green-600 focus:ring-green-500"
                                       required>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Full Database Backup</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Backup semua tabel database (users, pesertas, activity_logs, settings, backups, dll)
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Format: SQL | Cocok untuk backup lengkap sistem
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="type === 'peserta' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                   @click="type = 'peserta'">
                                <input type="radio" 
                                       name="type" 
                                       value="peserta" 
                                       x-model="type"
                                       class="mt-1 mr-3 text-green-600 focus:ring-green-500"
                                       required>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Backup Data Peserta Undian</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Backup khusus data peserta undian (semua data peserta termasuk yang sudah dihapus)
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Format: SQL atau Excel | Cocok untuk backup/arsip data peserta
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Format Selection (only for peserta) -->
                    <div x-show="type === 'peserta'"
                         x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Format File <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition"
                                   :class="format === 'sql' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                   @click="format = 'sql'">
                                <input type="radio" 
                                       name="format" 
                                       value="sql" 
                                       x-model="format"
                                       class="mr-2 text-green-600 focus:ring-green-500"
                                       required>
                                <div>
                                    <div class="font-medium text-gray-900">SQL</div>
                                    <div class="text-xs text-gray-500">Untuk restore ke database</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition"
                                   :class="format === 'excel' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                   @click="format = 'excel'">
                                <input type="radio" 
                                       name="format" 
                                       value="excel" 
                                       x-model="format"
                                       class="mr-2 text-green-600 focus:ring-green-500"
                                       required>
                                <div>
                                    <div class="font-medium text-gray-900">Excel</div>
                                    <div class="text-xs text-gray-500">Untuk dibuka di Excel</div>
                                </div>
                            </label>
                        </div>
                        @error('format')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi (Opsional)
                        </label>
                        <textarea name="description" 
                                  rows="3"
                                  placeholder="Contoh: Backup sebelum update sistem, Backup harian, Backup data peserta akhir tahun, dll"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Tambahkan catatan untuk membantu mengidentifikasi backup ini di kemudian hari</p>
                    </div>

                    <!-- Dynamic Info Card -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Informasi Backup</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Tipe Backup:</dt>
                                <dd class="font-medium text-gray-900" x-text="type === 'full' ? 'Full Database Backup' : 'Backup Data Peserta Undian'"></dd>
                            </div>
                            <div class="flex justify-between" x-show="type === 'peserta'">
                                <dt class="text-gray-600">Format File:</dt>
                                <dd class="font-medium text-gray-900" x-text="format === 'sql' ? 'SQL' : 'Excel (.xlsx)'"></dd>
                            </div>
                            <div class="flex justify-between" x-show="type === 'full'">
                                <dt class="text-gray-600">Format File:</dt>
                                <dd class="font-medium text-gray-900">SQL</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Cakupan:</dt>
                                <dd class="font-medium text-green-600" x-text="type === 'full' ? 'Semua Tabel Database' : 'Tabel Pesertas (Semua Data)'"></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Lokasi Penyimpanan:</dt>
                                <dd class="font-medium text-gray-900">storage/app/backups</dd>
                            </div>
                        </dl>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-600" x-show="type === 'full'">
                                <strong>Catatan:</strong> Backup akan mencakup semua tabel database (users, pesertas, activity_logs, settings, backups, dll) 
                                kecuali tabel migrations untuk menghindari konflik saat restore.
                            </p>
                            <p class="text-xs text-gray-600" x-show="type === 'peserta'">
                                <strong>Catatan:</strong> Backup akan mencakup semua data peserta undian termasuk:
                                <br>• Semua kolom (ID, No Rekening, Nama, Alamat, Cabang, Status Menang, Hadiah, Waktu Menang, dll)
                                <br>• Data yang sudah dihapus (soft deleted)
                                <br>• Timestamps lengkap (Dibuat, Diperbarui, Dihapus)
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.backups.index') }}" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                <span>Buat Backup Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

