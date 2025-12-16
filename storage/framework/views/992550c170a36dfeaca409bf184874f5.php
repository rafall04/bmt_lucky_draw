<?php
    // Determine background style
    $bgStyle = '';
    $hasBackgroundImage = false;
    if ($undianBackgroundType === 'image' && $undianBackgroundImagePath && \Storage::disk('public')->exists($undianBackgroundImagePath)) {
        $bgStyle = 'background-image: url(' . \Storage::url($undianBackgroundImagePath) . '); background-size: cover; background-position: center; background-repeat: no-repeat;';
        $hasBackgroundImage = true;
    } elseif ($undianBackgroundType === 'color') {
        // Check if gradient colors are set
        if ($undianBackgroundGradientFrom && $undianBackgroundGradientTo && $undianBackgroundGradientFrom !== $undianBackgroundGradientTo) {
            $bgStyle = "background: linear-gradient(to bottom right, {$undianBackgroundGradientFrom}, {$undianBackgroundGradientTo});";
        } else {
            $bgStyle = "background-color: {$undianBackgroundColor};";
        }
    } else {
        // Default fallback
        $bgStyle = 'background: linear-gradient(to bottom right, #009900, #006600);';
    }
?>
<div class="h-screen w-full overflow-hidden relative flex flex-col"
     style="<?php echo e($bgStyle); ?>"
     wire:id="<?php echo e($this->getId()); ?>"
     data-dummy="<?php echo e(base64_encode(json_encode($dummyData))); ?>"
     x-data="undianData()"
     x-on:winner-selected.window="handleWinnerSelected($event)">
    <!-- Dark Overlay for Background Image (to ensure text readability) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBackgroundImage): ?>
        <div class="absolute inset-0 bg-black/40 z-0 pointer-events-none"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <!-- Floating Confetti/Ribbons Effect -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-400/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-32 right-20 w-16 h-16 bg-blue-400/20 rounded-full blur-xl animate-pulse" style="animation-delay: 0.5s;"></div>
        <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-red-400/20 rounded-full blur-xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-40 right-1/3 w-18 h-18 bg-purple-400/20 rounded-full blur-xl animate-pulse" style="animation-delay: 1.5s;"></div>
    </div>
    
    <!-- Header Section with Top Spacing -->
    <header class="relative z-10 pt-6 lg:pt-8 pb-4 lg:pb-5 flex items-center justify-between px-6 lg:px-8 flex-shrink-0">
        <!-- Left: Logo and Company Info -->
        <div class="flex items-center space-x-3 lg:space-x-4">
            <div class="bg-white rounded-full p-2 lg:p-3 shadow-lg flex-shrink-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoPath && \Storage::disk('public')->exists($logoPath)): ?>
                    <img src="<?php echo e(\Storage::url($logoPath)); ?>" 
                         alt="Logo BMT NU" 
                         class="w-12 h-12 lg:w-16 lg:h-16 object-contain rounded-full">
                <?php else: ?>
                    <div class="w-12 h-12 lg:w-16 lg:h-16 bg-green-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg lg:text-xl">BMT</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex-shrink-0">
                <h2 class="text-white text-xs lg:text-sm font-medium leading-tight">KOPERASI SYARIAH</h2>
                <h1 class="text-white text-lg lg:text-2xl font-bold leading-tight"><?php echo e($undianCompanyName); ?></h1>
                <p class="text-green-200 text-[10px] lg:text-xs leading-tight"><?php echo e($undianCompanyTagline); ?></p>
            </div>
        </div>

        <!-- Right: Registration Badge -->
        <div class="text-right flex-shrink-0">
            <div class="flex items-center justify-end space-x-1 lg:space-x-2 text-white text-[10px] lg:text-xs mb-0.5">
                <span class="text-base lg:text-xl">#</span>
                <span>Terdaftar di KEMEN KOPUKM</span>
            </div>
            <p class="text-green-200 text-[9px] lg:text-xs leading-tight">Kementerian Koperasi dan UKM Republik Indonesia</p>
        </div>
    </header>

    <!-- Main Content Section (Flex-1 - takes remaining space) -->
    <main class="relative z-10 flex-1 w-full flex items-center justify-center px-4 lg:px-8 gap-4 lg:gap-8 overflow-hidden py-4 lg:py-6" style="min-height: 0;">
        <div class="grid grid-cols-12 w-full h-full max-h-full gap-4 lg:gap-8">
            <!-- Left Column: Undian Section (Span 7) -->
            <div class="col-span-12 lg:col-span-7 flex flex-col items-center justify-center h-full">
                <!-- Undian Title -->
                <h2 class="text-white text-2xl lg:text-4xl font-bold mb-3 lg:mb-4 leading-tight"><?php echo e($undianTitleText); ?></h2>
                
                <!-- Large Number Display -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl lg:rounded-2xl p-4 lg:p-6 mb-4 lg:mb-6 border-2 border-white/30 shadow-2xl w-full max-w-2xl">
                    <p class="text-white text-4xl lg:text-6xl xl:text-7xl font-bold text-center tracking-wider leading-tight" 
                       x-text="displayNo"
                       style="font-family: 'Courier New', monospace; letter-spacing: 0.1em; min-height: 60px; lg:min-height: 80px; display: flex; align-items: center; justify-content: center; word-break: break-all; text-shadow: 0 0 20px rgba(255,255,255,0.3);">
                    </p>
                </div>

                <!-- White Information Box -->
                <div class="bg-white/95 backdrop-blur shadow-2xl rounded-2xl lg:rounded-3xl p-4 lg:p-6 mb-4 lg:mb-6 transform transition-all duration-300 w-full max-w-2xl"
                     x-show="displayName && displayName !== '---' && !rolling"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="space-y-2 lg:space-y-3">
                        <div>
                            <p class="text-green-800 text-xl lg:text-3xl font-extrabold mb-1 lg:mb-2 leading-tight" x-text="displayName" style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></p>
                        </div>
                        <div class="border-t border-gray-200 pt-2 lg:pt-3">
                            <p class="text-gray-700 text-sm lg:text-base leading-tight">
                                <span class="font-semibold text-gray-800">Alamat:</span> 
                                <span x-text="displayAlamat" class="text-gray-600"></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-700 text-sm lg:text-base leading-tight">
                                <span class="font-semibold text-gray-800">Anggota</span> 
                                <span class="text-gray-600">PEMBIAYAAN</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-700 text-sm lg:text-base leading-tight">
                                <span class="font-semibold text-gray-800">CABANG</span> 
                                <span x-text="displayCabang !== '---' ? displayCabang : ''" class="text-gray-600"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Single Action Button (One-Click Cycle) -->
                <div class="flex justify-center">
                    <button 
                        type="button"
                        @click="rolling ? handleStopClick() : startRolling()"
                        x-bind:disabled="<?php echo \Illuminate\Support\Js::from($is_processing)->toHtml() ?> || waitingForResult || stopping"
                        :class="rolling ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        class="text-white font-bold py-3 lg:py-4 px-8 lg:px-12 rounded-full shadow-[0_10px_20px_rgba(0,0,0,0.5)] transition-all transform hover:scale-105 active:scale-95 text-lg lg:text-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        style="box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.2); min-width: 180px; lg:min-width: 200px;">
                        <span x-show="!rolling && !waitingForResult && !stopping">START</span>
                        <span x-show="rolling && !waitingForResult && !stopping">STOP</span>
                        <span x-show="waitingForResult || stopping" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 lg:h-5 lg:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            MEMPROSES...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Doorprize Image Only (Span 5) -->
            <div class="col-span-12 lg:col-span-5 flex flex-col justify-center items-center h-full overflow-hidden">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doorprizeImagePath && \Storage::disk('public')->exists($doorprizeImagePath)): ?>
                    <!-- Display Uploaded Doorprize Image -->
                    <div class="flex justify-center items-center w-full h-full p-4 lg:p-6">
                        <img src="<?php echo e(\Storage::url($doorprizeImagePath)); ?>" 
                             alt="Foto Doorprize" 
                             class="max-h-full max-w-full w-auto h-auto object-contain rounded-lg shadow-2xl">
                    </div>
                <?php else: ?>
                    <!-- Empty State - No Image -->
                    <div class="flex justify-center items-center w-full h-full p-4 lg:p-6">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl lg:rounded-2xl p-8 lg:p-12 border-2 border-white/10 w-full h-full flex items-center justify-center">
                            <p class="text-white/50 text-sm lg:text-base text-center">Foto doorprize akan ditampilkan di sini</p>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Flash Messages (Fixed Position) -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 pointer-events-none">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
            <div class="bg-green-500 text-white px-6 py-4 rounded-lg text-center shadow-lg">
                <p class="font-semibold"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(session()->has('error')): ?>
            <div class="bg-red-500 text-white px-6 py-4 rounded-lg text-center shadow-lg">
                <p class="font-semibold"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    <!-- Footer: Digital Services Bar (8% height) -->
    <footer class="relative z-10 h-[8%] flex items-center justify-between px-4 lg:px-6 bg-green-900/95 backdrop-blur-sm border-t-2 border-green-700 flex-shrink-0">
        <div class="flex flex-wrap items-center justify-center gap-3 lg:gap-6 w-full">
            <!-- ATM Icon -->
            <div class="flex items-center space-x-1 lg:space-x-2 text-white">
                <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs lg:text-sm font-medium"><?php echo e($undianFooterAtmLabel); ?></span>
            </div>
            
            <!-- Mobile Icon -->
            <div class="flex items-center space-x-1 lg:space-x-2 text-white">
                <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                </svg>
                <span class="text-xs lg:text-sm font-medium"><?php echo e($undianFooterMobileLabel); ?></span>
            </div>
            
            <!-- Baitul Maal Icon -->
            <div class="flex items-center space-x-1 lg:space-x-2 text-white">
                <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs lg:text-sm font-medium"><?php echo e($undianFooterBaitulMaalLabel); ?></span>
            </div>
            
            <!-- Text -->
            <div class="text-center lg:text-left">
                <p class="text-white text-[10px] lg:text-sm font-semibold leading-tight">
                    <?php echo e($undianFooterText); ?>

                </p>
            </div>
        </div>
    </footer>
</div>
<?php /**PATH C:\project\bmtnu\resources\views/livewire/undian.blade.php ENDPATH**/ ?>