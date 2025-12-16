<x-admin-layout title="Edit Peserta">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('admin.pesertas.index') }}" 
               class="text-green-600 hover:text-green-700 font-semibold flex items-center space-x-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Kembali ke Daftar Peserta</span>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Peserta</h1>
            <p class="text-gray-600 mt-2">Perbarui informasi peserta</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('admin.pesertas.update', $peserta) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Rekening <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="no_rekening" 
                               value="{{ old('no_rekening', $peserta->no_rekening) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('no_rekening')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama" 
                               value="{{ old('nama', $peserta->nama) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                        <textarea name="alamat" 
                                  rows="3"
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('alamat', $peserta->alamat) }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                        <input type="text" 
                               name="cabang" 
                               value="{{ old('cabang', $peserta->cabang) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('cabang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4">
                        <a href="{{ route('admin.pesertas.index') }}" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition">
                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

