<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => 'Pengaturan'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600 mt-2">Konfigurasi Telegram, Logo, Foto Doorprize, dan Customisasi Halaman Undian</p>
        </div>

        <!-- Info Card -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 mb-1">Cara Mendapatkan Bot Token & Chat ID</h3>
                    <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                        <li>Buka Telegram dan cari <strong>@BotFather</strong></li>
                        <li>Kirim perintah <code class="bg-blue-100 px-1 rounded">/newbot</code> dan ikuti instruksi</li>
                        <li>Salin <strong>Bot Token</strong> yang diberikan BotFather</li>
                        <li>Untuk Chat ID: Tambahkan bot ke grup, lalu kirim pesan ke grup</li>
                        <li>Buka <code class="bg-blue-100 px-1 rounded">https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code></li>
                        <li>Cari <strong>"chat":{"id"</strong> - angka setelahnya adalah Chat ID</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="space-y-8">
                    <!-- Telegram Settings Section -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Pengaturan Telegram</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Bot Token
                                </label>
                                <input type="text" 
                                       name="telegram_bot_token" 
                                       value="<?php echo e(old('telegram_bot_token', $telegramBotToken)); ?>"
                                       placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm">
                                <p class="mt-1 text-xs text-gray-500">Token bot Telegram dari BotFather</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['telegram_bot_token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Chat ID
                                </label>
                                <input type="text" 
                                       name="telegram_chat_id" 
                                       value="<?php echo e(old('telegram_chat_id', $telegramChatId)); ?>"
                                       placeholder="-1001234567890"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm">
                                <p class="mt-1 text-xs text-gray-500">ID grup atau chat untuk menerima notifikasi</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['telegram_chat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Upload Section -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Logo BMT NU</h2>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoPath): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-700">Logo Saat Ini</span>
                                        <form action="<?php echo e(route('admin.settings.delete-logo')); ?>" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus logo?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Hapus Logo
                                            </button>
                                        </form>
                                    </div>
                                    <div class="flex justify-center">
                                        <img src="<?php echo e(\Storage::url($logoPath)); ?>" alt="Logo BMT NU" class="max-h-32 max-w-full object-contain rounded-lg shadow-sm">
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Logo Baru
                                </label>
                                <input type="file" 
                                       name="logo" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Maksimal 2MB. Logo akan ditampilkan di header halaman undian.</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Doorprize Image Upload Section -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Foto Doorprize</h2>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doorprizeImagePath): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-700">Foto Doorprize Saat Ini</span>
                                        <form action="<?php echo e(route('admin.settings.delete-doorprize-image')); ?>" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus foto doorprize?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Hapus Foto
                                            </button>
                                        </form>
                                    </div>
                                    <div class="flex justify-center">
                                        <img src="<?php echo e(\Storage::url($doorprizeImagePath)); ?>" alt="Foto Doorprize" class="max-h-64 max-w-full object-contain rounded-lg shadow-sm">
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Foto Doorprize Baru
                                </label>
                                <input type="file" 
                                       name="doorprize_image" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Maksimal 5MB. Foto akan ditampilkan di bagian kanan halaman undian.</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['doorprize_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Undian Page Customization Section -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Customisasi Halaman Undian</h2>
                        <div class="space-y-6" x-data="{ backgroundType: '<?php echo e(old('undian_background_type', $undianBackgroundType)); ?>' }">
                            <!-- Background Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Tipe Background <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                           :class="backgroundType === 'color' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                           @click="backgroundType = 'color'">
                                        <input type="radio" 
                                               name="undian_background_type" 
                                               value="color" 
                                               x-model="backgroundType"
                                               class="mr-3 text-green-600 focus:ring-green-500"
                                               required>
                                        <div>
                                            <div class="font-medium text-gray-900">Warna / Gradient</div>
                                            <div class="text-xs text-gray-500">Gunakan warna solid atau gradient</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                           :class="backgroundType === 'image' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-500'"
                                           @click="backgroundType = 'image'">
                                        <input type="radio" 
                                               name="undian_background_type" 
                                               value="image" 
                                               x-model="backgroundType"
                                               class="mr-3 text-green-600 focus:ring-green-500"
                                               required>
                                        <div>
                                            <div class="font-medium text-gray-900">Foto Background</div>
                                            <div class="text-xs text-gray-500">Upload foto sebagai background</div>
                                        </div>
                                    </label>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_background_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Color Background Options -->
                            <div x-show="backgroundType === 'color'" x-cloak>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Warna Solid (Jika tidak menggunakan gradient)
                                        </label>
                                        <input type="color" 
                                               name="undian_background_color" 
                                               value="<?php echo e(old('undian_background_color', $undianBackgroundColor)); ?>"
                                               class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                                        <p class="mt-1 text-xs text-gray-500">Pilih warna solid untuk background</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Gradient Dari
                                            </label>
                                            <input type="color" 
                                                   name="undian_background_gradient_from" 
                                                   value="<?php echo e(old('undian_background_gradient_from', $undianBackgroundGradientFrom)); ?>"
                                                   class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Gradient Ke
                                            </label>
                                            <input type="color" 
                                                   name="undian_background_gradient_to" 
                                                   value="<?php echo e(old('undian_background_gradient_to', $undianBackgroundGradientTo)); ?>"
                                                   class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">Gradient akan digunakan jika kedua warna diisi. Jika hanya satu warna, gunakan warna solid.</p>
                                </div>
                            </div>

                            <!-- Image Background Option -->
                            <div x-show="backgroundType === 'image'" x-cloak>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($undianBackgroundImagePath): ?>
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-sm font-medium text-gray-700">Background Image Saat Ini</span>
                                                <form action="<?php echo e(route('admin.settings.delete-undian-background-image')); ?>" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus background image?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                        Hapus Background
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="flex justify-center">
                                                <img src="<?php echo e(\Storage::url($undianBackgroundImagePath)); ?>" alt="Background Image" class="max-h-48 max-w-full object-contain rounded-lg shadow-sm">
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Upload Background Image
                                        </label>
                                        <input type="file" 
                                               name="undian_background_image" 
                                               accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Maksimal 5MB. Background akan menutupi seluruh halaman undian.</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_background_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Text Customization -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Customisasi Teks</h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Judul Undian
                                    </label>
                                    <input type="text" 
                                           name="undian_title_text" 
                                           value="<?php echo e(old('undian_title_text', $undianTitleText)); ?>"
                                           placeholder="UNDIAN BERKAH"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <p class="mt-1 text-xs text-gray-500">Teks yang ditampilkan sebagai judul utama (contoh: "UNDIAN BERKAH")</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_title_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Perusahaan
                                    </label>
                                    <input type="text" 
                                           name="undian_company_name" 
                                           value="<?php echo e(old('undian_company_name', $undianCompanyName)); ?>"
                                           placeholder="BMT NU TEMAYANG"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <p class="mt-1 text-xs text-gray-500">Nama perusahaan yang ditampilkan di header</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tagline Perusahaan
                                    </label>
                                    <input type="text" 
                                           name="undian_company_tagline" 
                                           value="<?php echo e(old('undian_company_tagline', $undianCompanyTagline)); ?>"
                                           placeholder="Sudah Terbukti dan Teruji"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <p class="mt-1 text-xs text-gray-500">Tagline yang ditampilkan di bawah nama perusahaan</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_company_tagline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Teks Footer
                                    </label>
                                    <input type="text" 
                                           name="undian_footer_text" 
                                           value="<?php echo e(old('undian_footer_text', $undianFooterText)); ?>"
                                           placeholder="LAYANAN DIGITAL TERBAIK KOPSYAH BMT NU TEMAYANG"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <p class="mt-1 text-xs text-gray-500">Teks yang ditampilkan di footer halaman undian</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_footer_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <!-- Footer Labels Customization -->
                            <div class="space-y-4 pt-4 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Label Footer (Icon Labels)</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Label ATM
                                        </label>
                                        <input type="text" 
                                               name="undian_footer_atm_label" 
                                               value="<?php echo e(old('undian_footer_atm_label', $undianFooterAtmLabel)); ?>"
                                               placeholder="ATM"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <p class="mt-1 text-xs text-gray-500">Label untuk icon ATM</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_footer_atm_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Label Mobile
                                        </label>
                                        <input type="text" 
                                               name="undian_footer_mobile_label" 
                                               value="<?php echo e(old('undian_footer_mobile_label', $undianFooterMobileLabel)); ?>"
                                               placeholder="Mobile"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <p class="mt-1 text-xs text-gray-500">Label untuk icon Mobile</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_footer_mobile_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Label Baitul Maal
                                        </label>
                                        <input type="text" 
                                               name="undian_footer_baitul_maal_label" 
                                               value="<?php echo e(old('undian_footer_baitul_maal_label', $undianFooterBaitulMaalLabel)); ?>"
                                               placeholder="Baitul Maal"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <p class="mt-1 text-xs text-gray-500">Label untuk icon Baitul Maal</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['undian_footer_baitul_maal_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Setelah menyimpan, perubahan akan langsung terlihat di halaman undian.
                                </p>
                            </div>
                            <button type="submit" 
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Simpan Semua Pengaturan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Status Card -->
        <?php
            $isConfigured = !empty($telegramBotToken) && !empty($telegramChatId);
        ?>
        <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Konfigurasi</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Bot Token</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($telegramBotToken)): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Terkonfigurasi</span>
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Belum Dikonfigurasi</span>
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Chat ID</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($telegramChatId)): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Terkonfigurasi</span>
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Belum Dikonfigurasi</span>
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-900">Status Sistem</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isConfigured): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Siap Mengirim Notifikasi</span>
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Perlu Konfigurasi</span>
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>

<?php /**PATH C:\project\bmtnu\resources\views/admin/settings/edit.blade.php ENDPATH**/ ?>