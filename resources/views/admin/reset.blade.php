<x-admin-layout title="Reset Sistem">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Reset Sistem</h1>
            <p class="text-gray-600 mt-2">⚠️ Hati-hati! Tindakan ini tidak dapat dibatalkan</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Reset Status Menang -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-orange-300">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Reset Status Menang</h2>
                </div>
                <p class="text-gray-600 mb-4">
                    Mengembalikan semua status pemenang menjadi belum menang. Berguna untuk gladi resik atau acara ulang.
                </p>
                <form action="{{ route('admin.reset-pemenang') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Konfirmasi</label>
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="RESET_CONFIRM"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <button type="submit" 
                            onclick="return confirm('⚠️ PERINGATAN: Apakah Anda yakin ingin mereset semua status pemenang? Tindakan ini tidak dapat dibatalkan!')"
                            class="w-full px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition shadow-lg">
                        Reset Status Menang
                    </button>
                </form>
            </div>

            <!-- Truncate All Data -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-red-300">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Kosongkan Semua Data</h2>
                </div>
                <p class="text-gray-600 mb-4">
                    <strong class="text-red-600">DANGEROUS!</strong> Menghapus semua data peserta secara permanen. Hanya untuk acara tahun depan.
                </p>
                <form action="{{ route('admin.reset.truncate') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Konfirmasi</label>
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="TRUNCATE_CONFIRM"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   name="confirm" 
                                   required
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700">Saya mengerti bahwa tindakan ini tidak dapat dibatalkan</span>
                        </label>
                    </div>
                    <button type="submit" 
                            onclick="return confirm('⚠️⚠️⚠️ PERINGATAN KRITIS: Apakah Anda BENAR-BENAR yakin ingin menghapus SEMUA data peserta? Tindakan ini TIDAK DAPAT DIBATALKAN!')"
                            class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg">
                        KOSONGKAN SEMUA DATA
                    </button>
                </form>
            </div>
        </div>

        <!-- Test Telegram -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-blue-300">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Test Telegram</h2>
            </div>
            <p class="text-gray-600 mb-4">
                Kirim pesan test ke Telegram untuk memastikan koneksi berfungsi dengan baik.
            </p>
            <form action="{{ route('admin.reset.test-telegram') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg">
                    Kirim Test Notifikasi
                </button>
            </form>
        </div>

        <!-- Warning Box -->
        <div class="mt-8 bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-yellow-900 mb-2">Peringatan Penting</h3>
                    <ul class="text-sm text-yellow-800 space-y-1 list-disc list-inside">
                        <li>Pastikan Anda sudah melakukan backup data sebelum melakukan reset</li>
                        <li>Reset Status Menang hanya mengembalikan status, data peserta tetap ada</li>
                        <li>Kosongkan Semua Data akan menghapus SEMUA peserta secara permanen</li>
                        <li>Pastikan password konfirmasi benar sebelum submit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

