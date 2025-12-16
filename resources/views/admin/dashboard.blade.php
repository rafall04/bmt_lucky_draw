<x-admin-layout title="Dashboard">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-2">Ringkasan data dan statistik undian</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Peserta</p>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($totalPeserta) }}</p>
                </div>
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Pemenang</p>
                    <p class="text-4xl font-bold text-green-600">{{ number_format($totalPemenang) }}</p>
                </div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Kandidat Tersisa</p>
                    <p class="text-4xl font-bold text-orange-600">{{ number_format($remainingCandidates) }}</p>
                </div>
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Operator</p>
                    <p class="text-4xl font-bold text-purple-600">{{ number_format($totalOperators) }}</p>
                </div>
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Recent Winners -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Pemenang Terbaru</h2>
            </div>
            <div class="p-6">
                @if($recentWinners->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentWinners as $winner)
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $winner->nama }}</p>
                                    <p class="text-sm text-gray-600">{{ $winner->no_rekening }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $winner->waktu_menang ? format_wib($winner->waktu_menang, 'd/m/Y H:i') . ' WIB' : '-' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    @if($winner->hadiah_didapat)
                                        <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">
                                            {{ $winner->hadiah_didapat }}
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-semibold bg-gray-400 text-white rounded-full">
                                            Belum ada hadiah
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.winners') }}" 
                           class="text-green-600 hover:text-green-700 font-semibold text-sm">
                            Lihat Semua Pemenang →
                        </a>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Belum ada pemenang</p>
                @endif
            </div>
        </div>

        <!-- Prize Statistics -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Statistik Hadiah</h2>
            </div>
            <div class="p-6">
                @if($prizeStats->count() > 0)
                    <div class="space-y-4">
                        @foreach($prizeStats as $stat)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-medium">{{ $stat->hadiah_didapat }}</span>
                                <span class="px-4 py-1 bg-yellow-100 text-yellow-800 rounded-full font-bold">
                                    {{ $stat->count }} pemenang
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Belum ada data hadiah</p>
                @endif
            </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Aktivitas Terbaru</h2>
            </div>
            <div class="p-6">
                @if($recentLogs->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentLogs as $log)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($log->action === 'create')
                                        <span class="text-green-600 text-lg">✅</span>
                                    @elseif($log->action === 'update')
                                        <span class="text-yellow-600 text-lg">✏️</span>
                                    @elseif($log->action === 'delete')
                                        <span class="text-red-600 text-lg">🚨</span>
                                    @elseif($log->action === 'login')
                                        <span class="text-blue-600 text-lg">🔑</span>
                                    @elseif($log->action === 'logout')
                                        <span class="text-gray-600 text-lg">👋</span>
                                    @else
                                        <span class="text-gray-600 text-lg">📝</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $log->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $log->user ? $log->user->name : 'System' }} • 
                                        {{ format_wib($log->created_at, 'd/m/Y H:i') }} WIB
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.activity-logs') }}" 
                           class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
                            Lihat Semua Logs →
                        </a>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Belum ada aktivitas</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Aksi Cepat</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Quick Action: Undi Sekarang -->
            <a href="{{ route('home') }}" 
               class="border-2 border-dashed border-green-300 rounded-lg p-6 hover:bg-green-50 transition text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Undi Sekarang</h3>
                <p class="text-sm text-gray-600">Buka halaman undian</p>
            </a>

            <!-- Quick Action: Tambah Peserta -->
            <a href="{{ route('admin.pesertas.create') }}" 
               class="border-2 border-dashed border-blue-300 rounded-lg p-6 hover:bg-blue-50 transition text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tambah Peserta</h3>
                <p class="text-sm text-gray-600">Tambah peserta baru secara manual</p>
            </a>

            <!-- Quick Action: Lihat Pemenang -->
            <a href="{{ route('admin.winners') }}" 
               class="border-2 border-dashed border-yellow-300 rounded-lg p-6 hover:bg-yellow-50 transition text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Lihat Pemenang</h3>
                <p class="text-sm text-gray-600">Daftar semua pemenang</p>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Import Excel -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Import Data Peserta</h3>
                <form action="{{ route('admin.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <input type="file" 
                               name="file" 
                               accept=".xlsx,.xls"
                               required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-lg">
                        Import Excel
                    </button>
                </form>
                <a href="{{ route('admin.import-info') }}" 
                   class="mt-3 block text-center text-sm text-green-600 hover:text-green-700">
                    Lihat format file →
                </a>
            </div>

            <!-- Reset Pemenang - Only for Admin -->
            @if(auth()->user()->isAdmin())
                <div class="border-2 border-dashed border-red-300 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reset Semua Pemenang</h3>
                    <form action="{{ route('admin.reset-pemenang') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <input type="password" 
                                   name="password" 
                                   required
                                   placeholder="Password: RESET_CONFIRM"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        <button type="submit" 
                                onclick="return confirm('⚠️ PERINGATAN: Apakah Anda yakin ingin mereset semua pemenang? Tindakan ini tidak dapat dibatalkan!')"
                                class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg">
                            Reset Pemenang
                        </button>
                    </form>
                </div>
            @else
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Reset Semua Pemenang</h3>
                    <p class="text-sm text-gray-600 mb-4">Fitur ini hanya tersedia untuk Admin.</p>
                    <div class="px-4 py-2 bg-gray-200 rounded-lg text-center">
                        <span class="text-gray-500 font-medium">Akses Terbatas</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
