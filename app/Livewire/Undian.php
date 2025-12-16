<?php

namespace App\Livewire;

use App\Models\Peserta;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Undian extends Component
{
    public $pemenang = null;
    public $is_rolling = false;
    public $hadiah_selected = '';
    public $dummyData = [];
    public $is_processing = false;

    /**
     * Get dummy data for rolling animation from actual database.
     * This ensures the rolling animation shows real participant data.
     */
    public function getDummyData()
    {
        return Cache::remember('undian_dummy_data', 60, function () {
            return Peserta::where('status_menang', 0)
                ->select('id', 'no_rekening', 'nama', 'alamat', 'cabang')
                ->inRandomOrder()
                ->limit(50)
                ->get()
                ->map(function ($peserta) {
                    return [
                        'no_rekening' => $peserta->no_rekening,
                        'nama' => $peserta->nama,
                        'alamat' => $peserta->alamat,
                        'cabang' => $peserta->cabang ?? '',
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Pick a random winner from eligible participants using efficient method.
     * Uses random ID range selection for optimal performance with large datasets.
     */
    public function pickWinner()
    {
        if ($this->is_processing) {
            return;
        }

        $this->is_processing = true;

        try {
            $peserta = null;
            $pesertaId = null;
            
            DB::beginTransaction();
            
            try {
                $minMax = Peserta::where('status_menang', 0)
                    ->selectRaw('MIN(id) as min_id, MAX(id) as max_id, COUNT(*) as count')
                    ->first();

                if (!$minMax || $minMax->count === 0) {
                    DB::rollBack();
                    session()->flash('error', 'Tidak ada peserta yang tersedia untuk diundi!');
                    $this->is_rolling = false;
                    return;
                }

                $selectedPeserta = null;
                $attempts = 0;
                $maxAttempts = 10;

                // Try to find a random participant using ID range
                while (!$selectedPeserta && $attempts < $maxAttempts) {
                    $randomId = rand($minMax->min_id, $minMax->max_id);

                    $selectedPeserta = Peserta::where('status_menang', 0)
                        ->where('id', '>=', $randomId)
                        ->select('id', 'no_rekening', 'nama', 'alamat', 'cabang', 'status_menang', 'waktu_menang')
                        ->lockForUpdate()
                        ->first();

                    if (!$selectedPeserta) {
                        $selectedPeserta = Peserta::where('status_menang', 0)
                            ->where('id', '>=', $minMax->min_id)
                            ->select('id', 'no_rekening', 'nama', 'alamat', 'cabang', 'status_menang', 'waktu_menang')
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($selectedPeserta && $selectedPeserta->status_menang) {
                        $selectedPeserta = null;
                    }

                    $attempts++;
                }

                if (!$selectedPeserta) {
                    $selectedPeserta = Peserta::where('status_menang', 0)
                        ->select('id', 'no_rekening', 'nama', 'alamat', 'cabang', 'status_menang', 'waktu_menang')
                        ->inRandomOrder()
                        ->lockForUpdate()
                        ->first();
                }

                if (!$selectedPeserta) {
                    DB::rollBack();
                    session()->flash('error', 'Tidak ada peserta yang tersedia untuk diundi!');
                    $this->is_rolling = false;
                    return;
                }

                $pesertaId = $selectedPeserta->id;

                $selectedPeserta->status_menang = 1;
                $selectedPeserta->waktu_menang = now();
                $saved = $selectedPeserta->save();

                if (!$saved) {
                    DB::rollBack();
                    \Log::error('pickWinner: Save model gagal', [
                        'peserta_id' => $pesertaId,
                    ]);
                    throw new \Exception('Gagal menyimpan data pemenang - save model gagal');
                }

                if ($selectedPeserta->status_menang != 1 || !$selectedPeserta->waktu_menang) {
                    DB::rollBack();
                    \Log::error('pickWinner: Model attributes tidak ter-set dengan benar', [
                        'peserta_id' => $pesertaId,
                        'status_menang' => $selectedPeserta->status_menang,
                        'waktu_menang' => $selectedPeserta->waktu_menang,
                    ]);
                    throw new \Exception('Gagal menyimpan data pemenang - model attributes tidak ter-set');
                }

                DB::commit();

                $peserta = $selectedPeserta;
                Cache::forget('undian_dummy_data');
                $peserta->refresh();
                
                $this->pemenang = $peserta;
                $this->is_rolling = false;
                
                $this->dispatch('winner-selected', pemenang: [
                    'no_rekening' => $peserta->no_rekening,
                    'nama' => $peserta->nama,
                    'alamat' => $peserta->alamat,
                    'cabang' => $peserta->cabang ?? '',
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            $this->is_rolling = false;
            \Log::error('pickWinner: Exception terjadi', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            $this->is_rolling = false;
            \Log::error('pickWinner: Throwable terjadi', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->is_processing = false;
        }
    }

    /**
     * Update the prize for the already-selected winner.
     */
    public function saveWinner()
    {
        if (!$this->pemenang) {
            session()->flash('error', 'Tidak ada pemenang yang dipilih!');
            return;
        }

        if (empty($this->hadiah_selected)) {
            session()->flash('error', 'Silakan pilih hadiah terlebih dahulu!');
            return;
        }

        try {
            $this->pemenang->update([
                'hadiah_didapat' => $this->hadiah_selected,
            ]);

            $this->pemenang = $this->pemenang->fresh();
            session()->flash('success', 'Hadiah berhasil disimpan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Reset display without altering database.
     */
    public function resetDisplay()
    {
        $this->pemenang = null;
        $this->hadiah_selected = '';
        $this->is_rolling = false;
    }

    /**
     * Start rolling animation.
     */
    public function startRolling()
    {
        $this->is_rolling = true;
        $this->pemenang = null;
        $this->hadiah_selected = '';
    }

    public function render()
    {
        $this->dummyData = $this->getDummyData();

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

        return view('livewire.undian', [
            'dummyData' => $this->dummyData,
            'logoPath' => $logoPath,
            'doorprizeImagePath' => $doorprizeImagePath,
            'undianBackgroundType' => $undianBackgroundType,
            'undianBackgroundColor' => $undianBackgroundColor,
            'undianBackgroundGradientFrom' => $undianBackgroundGradientFrom,
            'undianBackgroundGradientTo' => $undianBackgroundGradientTo,
            'undianBackgroundImagePath' => $undianBackgroundImagePath,
            'undianTitleText' => $undianTitleText,
            'undianCompanyName' => $undianCompanyName,
            'undianCompanyTagline' => $undianCompanyTagline,
            'undianFooterText' => $undianFooterText,
            'undianFooterAtmLabel' => $undianFooterAtmLabel,
            'undianFooterMobileLabel' => $undianFooterMobileLabel,
            'undianFooterBaitulMaalLabel' => $undianFooterBaitulMaalLabel,
        ])
            ->layout('layouts.app');
    }
}
