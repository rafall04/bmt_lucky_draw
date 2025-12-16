<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show the settings form.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $telegramBotToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', ''));
        $telegramChatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id', ''));
        $logoPath = Setting::get('logo_path', '');
        $doorprizeImagePath = Setting::get('doorprize_image_path', '');
        
        $undianBackgroundType = Setting::get('undian_background_type', 'color');
        $undianBackgroundColor = Setting::get('undian_background_color', '#006600');
        $undianBackgroundGradientFrom = Setting::get('undian_background_gradient_from', '#009900');
        $undianBackgroundGradientTo = Setting::get('undian_background_gradient_to', '#006600');
        $undianBackgroundImagePath = Setting::get('undian_background_image_path', '');
        $undianTitleText = Setting::get('undian_title_text', 'UNDIAN BERKAH');
        $undianCompanyName = Setting::get('undian_company_name', 'BMT NU TEMAYANG');
        $undianCompanyTagline = Setting::get('undian_company_tagline', 'Sudah Terbukti dan Teruji');
        $undianFooterText = Setting::get('undian_footer_text', 'LAYANAN DIGITAL TERBAIK KOPSYAH BMT NU TEMAYANG');
        $undianFooterAtmLabel = Setting::get('undian_footer_atm_label', 'ATM');
        $undianFooterMobileLabel = Setting::get('undian_footer_mobile_label', 'Mobile');
        $undianFooterBaitulMaalLabel = Setting::get('undian_footer_baitul_maal_label', 'Baitul Maal');

        return view('admin.settings.edit', compact(
            'telegramBotToken', 
            'telegramChatId', 
            'logoPath', 
            'doorprizeImagePath',
            'undianBackgroundType',
            'undianBackgroundColor',
            'undianBackgroundGradientFrom',
            'undianBackgroundGradientTo',
            'undianBackgroundImagePath',
            'undianTitleText',
            'undianCompanyName',
            'undianCompanyTagline',
            'undianFooterText',
            'undianFooterAtmLabel',
            'undianFooterMobileLabel',
            'undianFooterBaitulMaalLabel'
        ));
    }

    /**
     * Update settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'doorprize_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'undian_background_type' => 'required|in:color,image',
            'undian_background_color' => 'nullable|string|max:7',
            'undian_background_gradient_from' => 'nullable|string|max:7',
            'undian_background_gradient_to' => 'nullable|string|max:7',
            'undian_background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'undian_title_text' => 'nullable|string|max:100',
            'undian_company_name' => 'nullable|string|max:100',
            'undian_company_tagline' => 'nullable|string|max:200',
            'undian_footer_text' => 'nullable|string|max:200',
            'undian_footer_atm_label' => 'nullable|string|max:50',
            'undian_footer_mobile_label' => 'nullable|string|max:50',
            'undian_footer_baitul_maal_label' => 'nullable|string|max:50',
        ]);

        try {
            // Update Telegram settings
            Setting::set('telegram_bot_token', $request->telegram_bot_token ?? '');
            Setting::set('telegram_chat_id', $request->telegram_chat_id ?? '');

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Ensure images directory exists
                if (!Storage::disk('public')->exists('images')) {
                    Storage::disk('public')->makeDirectory('images');
                }

                $oldLogoPath = Setting::get('logo_path', '');
                if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }

                $logoPath = $request->file('logo')->store('images', 'public');
                Setting::set('logo_path', $logoPath);
            }

            if ($request->hasFile('doorprize_image')) {
                if (!Storage::disk('public')->exists('images')) {
                    Storage::disk('public')->makeDirectory('images');
                }

                $oldDoorprizePath = Setting::get('doorprize_image_path', '');
                if ($oldDoorprizePath && Storage::disk('public')->exists($oldDoorprizePath)) {
                    Storage::disk('public')->delete($oldDoorprizePath);
                }

                $doorprizePath = $request->file('doorprize_image')->store('images', 'public');
                Setting::set('doorprize_image_path', $doorprizePath);
            }

            Setting::set('undian_background_type', $request->undian_background_type ?? 'color');
            
            if ($request->undian_background_type === 'color') {
                Setting::set('undian_background_color', $request->undian_background_color ?? '#006600');
                Setting::set('undian_background_gradient_from', $request->undian_background_gradient_from ?? '#009900');
                Setting::set('undian_background_gradient_to', $request->undian_background_gradient_to ?? '#006600');
            }
            
            if ($request->hasFile('undian_background_image')) {
                if (!Storage::disk('public')->exists('images')) {
                    Storage::disk('public')->makeDirectory('images');
                }

                $oldBackgroundPath = Setting::get('undian_background_image_path', '');
                if ($oldBackgroundPath && Storage::disk('public')->exists($oldBackgroundPath)) {
                    Storage::disk('public')->delete($oldBackgroundPath);
                }

                $backgroundPath = $request->file('undian_background_image')->store('images', 'public');
                Setting::set('undian_background_image_path', $backgroundPath);
            }
            
            Setting::set('undian_title_text', $request->undian_title_text ?? 'UNDIAN BERKAH');
            Setting::set('undian_company_name', $request->undian_company_name ?? 'BMT NU TEMAYANG');
            Setting::set('undian_company_tagline', $request->undian_company_tagline ?? 'Sudah Terbukti dan Teruji');
            Setting::set('undian_footer_text', $request->undian_footer_text ?? 'LAYANAN DIGITAL TERBAIK KOPSYAH BMT NU TEMAYANG');
            Setting::set('undian_footer_atm_label', $request->undian_footer_atm_label ?? 'ATM');
            Setting::set('undian_footer_mobile_label', $request->undian_footer_mobile_label ?? 'Mobile');
            Setting::set('undian_footer_baitul_maal_label', $request->undian_footer_baitul_maal_label ?? 'Baitul Maal');

            log_activity('update', 'Mengubah pengaturan sistem (Telegram, Logo, Doorprize, Customisasi Undian)');

            return redirect()->route('admin.settings.edit')
                ->with('success', 'Pengaturan berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Failed to update settings', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.settings.edit')
                ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Delete logo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLogo()
    {
        try {
            $logoPath = Setting::get('logo_path', '');
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            Setting::set('logo_path', '');

            log_activity('update', 'Menghapus logo');

            return redirect()->route('admin.settings.edit')
                ->with('success', 'Logo berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Failed to delete logo', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.settings.edit')
                ->with('error', 'Gagal menghapus logo: ' . $e->getMessage());
        }
    }

    /**
     * Delete doorprize image.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDoorprizeImage()
    {
        try {
            $doorprizePath = Setting::get('doorprize_image_path', '');
            if ($doorprizePath && Storage::disk('public')->exists($doorprizePath)) {
                Storage::disk('public')->delete($doorprizePath);
            }
            Setting::set('doorprize_image_path', '');

            log_activity('update', 'Menghapus foto doorprize');

            return redirect()->route('admin.settings.edit')
                ->with('success', 'Foto doorprize berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Failed to delete doorprize image', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.settings.edit')
                ->with('error', 'Gagal menghapus foto doorprize: ' . $e->getMessage());
        }
    }

    /**
     * Delete undian background image.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUndianBackgroundImage()
    {
        try {
            $backgroundPath = Setting::get('undian_background_image_path', '');
            if ($backgroundPath && Storage::disk('public')->exists($backgroundPath)) {
                Storage::disk('public')->delete($backgroundPath);
            }
            Setting::set('undian_background_image_path', '');
            Setting::set('undian_background_type', 'color'); // Reset to color

            log_activity('update', 'Menghapus background image halaman undian');

            return redirect()->route('admin.settings.edit')
                ->with('success', 'Background image berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Failed to delete undian background image', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.settings.edit')
                ->with('error', 'Gagal menghapus background image: ' . $e->getMessage());
        }
    }
}
