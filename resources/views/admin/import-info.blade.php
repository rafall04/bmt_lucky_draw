<x-admin-layout title="Format Import Excel">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Format Import Excel</h1>
            <p class="text-gray-600 mt-2">Panduan format file Excel untuk import data peserta</p>
        </div>

        <!-- Format Information -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Format File Excel</h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">1. Header Baris Pertama</h3>
                    <p class="text-gray-600 mb-4">Baris pertama harus berisi header dengan kolom berikut (dalam urutan ini):</p>
                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                        <code class="text-sm font-mono text-gray-800">
                            no_rekening | nama | alamat | cabang
                        </code>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">2. Format Data</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 rounded-lg">
                            <thead class="bg-green-600 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left border border-gray-300">no_rekening</th>
                                    <th class="px-4 py-3 text-left border border-gray-300">nama</th>
                                    <th class="px-4 py-3 text-left border border-gray-300">alamat</th>
                                    <th class="px-4 py-3 text-left border border-gray-300">cabang</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr>
                                    <td class="px-4 py-3 border border-gray-300">123456789012</td>
                                    <td class="px-4 py-3 border border-gray-300">Ahmad Hidayat</td>
                                    <td class="px-4 py-3 border border-gray-300">Jl. Raya No. 123</td>
                                    <td class="px-4 py-3 border border-gray-300">CABANG TEMAYANG</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-3 border border-gray-300">987654321098</td>
                                    <td class="px-4 py-3 border border-gray-300">Siti Nurhaliza</td>
                                    <td class="px-4 py-3 border border-gray-300">Jl. Merdeka No. 45</td>
                                    <td class="px-4 py-3 border border-gray-300">CABANG BOJONEGORO</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">3. Ketentuan Kolom</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                <span class="text-green-600 font-bold text-sm">1</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">no_rekening (Wajib)</p>
                                <p class="text-gray-600 text-sm">Nomor rekening peserta. Harus unik dan tidak boleh duplikat.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                <span class="text-green-600 font-bold text-sm">2</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">nama (Wajib)</p>
                                <p class="text-gray-600 text-sm">Nama lengkap peserta.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                <span class="text-green-600 font-bold text-sm">3</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">alamat (Wajib)</p>
                                <p class="text-gray-600 text-sm">Alamat lengkap peserta.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                                <span class="text-yellow-600 font-bold text-sm">4</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">cabang (Opsional)</p>
                                <p class="text-gray-600 text-sm">Nama cabang. Boleh dikosongkan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Catatan Penting:</strong> Data dengan no_rekening yang sudah ada akan otomatis dilewati (tidak akan duplikat).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download Template -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Template Excel</h2>
            <p class="text-gray-600 mb-6">Download template Excel yang sudah disiapkan untuk memudahkan import data:</p>
            
            <div class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Template Import Peserta.xlsx</h3>
                        <p class="text-sm text-gray-600">File Excel dengan format yang sudah benar</p>
                    </div>
                    <a href="{{ route('admin.import.template') }}" 
                       class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Download Template</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Steps -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Langkah-langkah Import</h2>
            
            <div class="space-y-4">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                        1
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Siapkan File Excel</h3>
                        <p class="text-gray-600">Pastikan file Excel (.xlsx atau .xls) sudah sesuai dengan format yang dijelaskan di atas.</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                        2
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Buka Halaman Dashboard</h3>
                        <p class="text-gray-600">Kembali ke halaman Dashboard dan gunakan form "Import Data Peserta".</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                        3
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Upload File</h3>
                        <p class="text-gray-600">Pilih file Excel yang sudah disiapkan dan klik tombol "Import Excel".</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                        4
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Tunggu Proses</h3>
                        <p class="text-gray-600">Sistem akan memproses file dan menampilkan pesan sukses atau error jika ada masalah.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center space-x-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</x-admin-layout>

