<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> - <?php echo e(config('app.name', 'BMT Lucky Draw')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false, sidebarMobile: false }" @keydown.escape.window="sidebarMobile = false">
    <div class="min-h-screen flex">
        <!-- Mobile Overlay -->
        <div x-show="sidebarMobile" 
             x-cloak
             @click="sidebarMobile = false"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside :class="sidebarMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-br from-green-800 via-green-800 to-green-900 text-white h-screen overflow-hidden flex flex-col shadow-2xl transition-transform duration-300 ease-in-out">
            <!-- Top Section: Logo and Navigation -->
            <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-green-700 scrollbar-track-green-900">
                <div class="p-4">
                    <!-- Logo Section -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg border border-white/20">
                                <span class="text-white font-bold text-xl">BMT</span>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold tracking-tight">BMT Lucky Draw</h1>
                                <p class="text-xs text-green-200/80">Admin Panel</p>
                            </div>
                        </div>
                        <!-- Mobile Close Button -->
                        <button @click="sidebarMobile = false" class="lg:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-1.5 pb-6">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </a>

                        <a href="<?php echo e(route('admin.pesertas.index')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.pesertas.index') || request()->routeIs('admin.pesertas.create') || request()->routeIs('admin.pesertas.edit') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-medium">Data Peserta</span>
                        </a>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                            <a href="<?php echo e(route('admin.pesertas.trash')); ?>" 
                               class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.pesertas.trash') ? 'bg-red-500/20 text-white shadow-lg backdrop-blur-sm border border-red-400/30' : 'text-red-200/90 hover:bg-red-500/10 hover:text-white'); ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span class="font-medium">Trash Bin</span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <a href="<?php echo e(route('admin.winners')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.winners') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            <span class="font-medium">Daftar Pemenang</span>
                        </a>

                        <a href="<?php echo e(route('admin.profile.edit')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.profile.*') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="font-medium">Profil Saya</span>
                        </a>

                        <a href="<?php echo e(route('admin.activity-logs')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.activity-logs') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Activity Logs</span>
                        </a>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                            <div class="pt-4 pb-2">
                                <p class="px-4 text-xs font-semibold text-green-300/60 uppercase tracking-wider">Administrasi</p>
                            </div>
                            
                            <a href="<?php echo e(route('admin.settings.edit')); ?>" 
                               class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.settings.*') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="font-medium">Pengaturan</span>
                            </a>

                            <a href="<?php echo e(route('admin.users.index')); ?>" 
                               class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.users.*') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="font-medium">Manage Akun</span>
                            </a>

                            <a href="<?php echo e(route('admin.backups.index')); ?>" 
                               class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.backups.*') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                <span class="font-medium">Backup & Restore</span>
                            </a>

                            <a href="<?php echo e(route('admin.reset')); ?>" 
                               class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.reset*') ? 'bg-red-500/20 text-white shadow-lg backdrop-blur-sm border border-red-400/30' : 'text-red-200/90 hover:bg-red-500/10 hover:text-white'); ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="font-medium">Reset Sistem</span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-semibold text-green-300/60 uppercase tracking-wider">Lainnya</p>
                        </div>

                        <a href="<?php echo e(route('admin.import-info')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo e(request()->routeIs('admin.import-info') ? 'bg-white/20 text-white shadow-lg backdrop-blur-sm' : 'text-green-100/90 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Format Import</span>
                        </a>

                        <a href="<?php echo e(route('home')); ?>" 
                           class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 text-green-100/90 hover:bg-white/10 hover:text-white">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="font-medium">Kembali ke Undian</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Bottom Section: User Info and Logout -->
            <div class="flex-shrink-0 w-full p-4 border-t border-white/10 bg-green-900/50 backdrop-blur-sm">
                <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-white/10">
                    <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-sm"><?php echo e(substr(auth()->user()->name, 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate"><?php echo e(auth()->user()->name); ?></p>
                        <p class="text-xs text-green-200/70 truncate"><?php echo e(auth()->user()->email); ?></p>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium <?php echo e(auth()->user()->role === 'admin' ? 'bg-blue-500/20 text-blue-200 border border-blue-400/30' : 'bg-green-500/20 text-green-200 border border-green-400/30'); ?>">
                        <?php echo e(auth()->user()->role === 'admin' ? 'Admin' : 'Operator'); ?>

                    </span>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" 
                            class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-red-500/90 hover:bg-red-600 rounded-xl transition-all duration-200 text-sm font-semibold shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <!-- Top Navigation Bar (Mobile) -->
            <div class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <button @click="sidebarMobile = true" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">BMT</span>
                        </div>
                        <span class="font-semibold text-gray-900">Admin Panel</span>
                    </div>
                    <div class="w-10"></div>
                </div>
            </div>

            <div class="p-4">
                <!-- Flash Messages -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                    <div class="mb-4 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 lg:px-6 py-4 rounded-xl shadow-lg flex items-center justify-between animate-in slide-in-from-top duration-300">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium"><?php echo e(session('success')); ?></span>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(session()->has('error')): ?>
                    <div class="mb-4 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 lg:px-6 py-4 rounded-xl shadow-lg flex items-center justify-between animate-in slide-in-from-top duration-300">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium"><?php echo e(session('error')); ?></span>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo e($slot); ?>

            </div>
        </main>
    </div>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>

<?php /**PATH C:\project\bmtnu\resources\views/components/admin-layout.blade.php ENDPATH**/ ?>