<x-admin-layout title="Restore Database">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Restore Database</h1>
            <p class="text-gray-600 mt-2">Restore database dari backup yang dipilih</p>
        </div>

        <!-- Danger Warning -->
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-900 mb-1">PERINGATAN: Tindakan Berbahaya!</h3>
                    @if($backup->type === 'peserta')
                        <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                            <li>Restore akan <strong>MENGGANTI SELURUH DATA PESERTA</strong> dengan data dari backup</li>
                            <li>Semua data peserta yang dibuat setelah backup ini akan <strong>HILANG PERMANEN</strong></li>
                            <li>Data lain (users, settings, activity_logs) <strong>TIDAK AKAN TERPENGARUH</strong></li>
                            <li>Pastikan Anda sudah membuat backup terbaru sebelum melakukan restore</li>
                            <li>Tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong></li>
                        </ul>
                    @else
                        <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                            <li>Restore akan <strong>MENGGANTI SELURUH DATA</strong> di database dengan data dari backup</li>
                            <li>Semua data yang dibuat setelah backup ini akan <strong>HILANG PERMANEN</strong></li>
                            <li>Pastikan Anda sudah membuat backup terbaru sebelum melakukan restore</li>
                            <li>Tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong></li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Backup Info -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Backup</h3>
            <dl class="space-y-3">
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-600">Nama File:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $backup->filename }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-600">Tipe Backup:</dt>
                    <dd class="text-sm font-medium text-gray-900">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $backup->type === 'full' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $backup->type === 'full' ? 'Full Database' : 'Data Peserta' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-600">Ukuran:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $backup->formatted_size }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-600">Dibuat:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ format_wib($backup->created_at, 'd/m/Y H:i:s') }} WIB</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-600">Dibuat Oleh:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $backup->user->name }}</dd>
                </div>
                @if($backup->description)
                    <div class="flex justify-between items-start">
                        <dt class="text-sm text-gray-600">Deskripsi:</dt>
                        <dd class="text-sm font-medium text-gray-900 text-right">{{ $backup->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form action="{{ route('admin.backups.restore', $backup) }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   name="confirm" 
                                   value="1"
                                   required
                                   class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="text-sm font-medium text-gray-900">
                                Saya mengerti bahwa restore akan mengganti seluruh data database
                            </span>
                        </label>
                        @error('confirm')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="Masukkan password Anda untuk konfirmasi"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="mt-1 text-xs text-gray-500">Masukkan password akun Anda untuk mengkonfirmasi restore</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.backups.index') }}" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg transition flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span>Restore Database</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

