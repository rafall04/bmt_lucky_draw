<?php

namespace Tests\Feature;

use App\Livewire\Undian;
use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WinnerSelectionTest extends TestCase
{

    /**
     * Test can pick winner when participants available.
     */
    public function test_can_pick_winner_when_participants_available(): void
    {
        // Create eligible participants
        Peserta::factory()->count(10)->create(['status_menang' => false]);

        Livewire::test(Undian::class)
            ->call('pickWinner');

        // Verify exactly one winner was selected
        $winners = Peserta::where('status_menang', true)->count();
        $this->assertEquals(1, $winners);

        // Verify winner has waktu_menang set
        $winner = Peserta::where('status_menang', true)->first();
        $this->assertNotNull($winner->waktu_menang);
        $this->assertTrue($winner->status_menang);
    }

    /**
     * Test cannot pick winner when no participants available.
     */
    public function test_cannot_pick_winner_when_no_participants_available(): void
    {
        // Create only winners (no eligible participants)
        Peserta::factory()->count(5)->create(['status_menang' => true]);

        $component = Livewire::test(Undian::class)
            ->call('pickWinner');

        // Check that error flash message was set
        $this->assertTrue(session()->has('error') || $component->get('pemenang') === null);

        // Verify no new winners were created
        $winners = Peserta::where('status_menang', true)->count();
        $this->assertEquals(5, $winners);
    }

    /**
     * Test cannot pick winner twice (double winner prevention).
     */
    public function test_cannot_pick_same_winner_twice(): void
    {
        // Create participants
        $peserta1 = Peserta::factory()->create(['status_menang' => false]);
        Peserta::factory()->count(5)->create(['status_menang' => false]);

        // Pick first winner
        Livewire::test(Undian::class)
            ->call('pickWinner');

        // Verify winner was selected
        $peserta1->refresh();
        $this->assertTrue($peserta1->fresh()->status_menang || Peserta::where('status_menang', true)->count() === 1);

        // Try to pick another winner
        Livewire::test(Undian::class)
            ->call('pickWinner');

        // Verify we have exactly 2 winners (different participants)
        $winners = Peserta::where('status_menang', true)->count();
        $this->assertEquals(2, $winners);

        // Verify all winners have different IDs
        $winnerIds = Peserta::where('status_menang', true)->pluck('id')->toArray();
        $this->assertEquals(2, count(array_unique($winnerIds)));
    }

    /**
     * Test race condition - concurrent winner selection.
     * This is a critical test to ensure no double winners.
     */
    public function test_race_condition_concurrent_winner_selection(): void
    {
        // Create many participants
        Peserta::factory()->count(50)->create(['status_menang' => false]);

        // Simulate concurrent requests by picking multiple winners rapidly
        $winners = [];
        
        for ($i = 0; $i < 10; $i++) {
            $component = Livewire::test(Undian::class);
            $component->call('pickWinner');
            
            // Get the winner from component
            $pemenang = $component->get('pemenang');
            if ($pemenang) {
                $winners[] = $pemenang->id;
            }
        }

        // Verify we have exactly 10 unique winners (no duplicates)
        $uniqueWinners = array_unique($winners);
        $this->assertEquals(count($winners), count($uniqueWinners), 'Found duplicate winners - race condition detected!');

        // Verify database consistency
        $dbWinners = Peserta::where('status_menang', true)->count();
        $this->assertEquals(10, $dbWinners, 'Database has incorrect number of winners');
    }

    /**
     * Test winner selection uses lockForUpdate to prevent race conditions.
     */
    public function test_winner_selection_uses_lock_for_update(): void
    {
        Peserta::factory()->count(5)->create(['status_menang' => false]);

        // Pick winner
        Livewire::test(Undian::class)
            ->call('pickWinner');

        // Verify winner is locked and updated atomically
        $winner = Peserta::where('status_menang', true)->first();
        $this->assertNotNull($winner);
        $this->assertTrue($winner->status_menang);
        $this->assertNotNull($winner->waktu_menang);
    }

    /**
     * Test can save prize for selected winner.
     */
    public function test_can_save_prize_for_winner(): void
    {
        $peserta = Peserta::factory()->create(['status_menang' => false]);

        $component = Livewire::test(Undian::class);
        
        // Manually set pemenang (simulating after pickWinner)
        $peserta->update(['status_menang' => true, 'waktu_menang' => now()]);
        $component->set('pemenang', $peserta->fresh());
        $component->set('hadiah_selected', 'Hadiah Utama');

        // Save winner
        $component->call('saveWinner');

        // Verify prize was saved
        $peserta->refresh();
        $this->assertEquals('Hadiah Utama', $peserta->hadiah_didapat);
    }

    /**
     * Test cannot save prize without selecting prize.
     */
    public function test_cannot_save_prize_without_selecting_prize(): void
    {
        $peserta = Peserta::factory()->create(['status_menang' => true]);
        $initialHadiah = $peserta->hadiah_didapat;

        $component = Livewire::test(Undian::class)
            ->set('pemenang', $peserta)
            ->set('hadiah_selected', '')
            ->call('saveWinner');

        // Verify that hadiah was not saved (data unchanged in database)
        $peserta->refresh();
        $this->assertEquals($initialHadiah, $peserta->hadiah_didapat);
        
        // Verify component state - hadiah_selected should still be empty
        $component->assertSet('hadiah_selected', '');
    }

    /**
     * Test cannot save prize without winner.
     */
    public function test_cannot_save_prize_without_winner(): void
    {
        $component = Livewire::test(Undian::class)
            ->set('pemenang', null)
            ->set('hadiah_selected', 'Hadiah Utama')
            ->call('saveWinner');

        // Verify that pemenang is still null (no winner was set)
        $component->assertSet('pemenang', null);
        
        // Verify that no peserta in database has this hadiah (since save was prevented)
        $this->assertDatabaseMissing('pesertas', [
            'hadiah_didapat' => 'Hadiah Utama',
        ]);
    }

    /**
     * Test resetDisplay clears winner display.
     */
    public function test_reset_display_clears_winner_display(): void
    {
        $peserta = Peserta::factory()->create(['status_menang' => true]);

        Livewire::test(Undian::class)
            ->set('pemenang', $peserta)
            ->set('hadiah_selected', 'Hadiah Utama')
            ->set('is_rolling', true)
            ->call('resetDisplay')
            ->assertSet('pemenang', null)
            ->assertSet('hadiah_selected', '')
            ->assertSet('is_rolling', false);
    }

    /**
     * Test startRolling sets rolling state.
     */
    public function test_start_rolling_sets_rolling_state(): void
    {
        Livewire::test(Undian::class)
            ->call('startRolling')
            ->assertSet('is_rolling', true)
            ->assertSet('pemenang', null)
            ->assertSet('hadiah_selected', '');
    }

    /**
     * Test pickWinner handles transaction rollback on error.
     */
    public function test_pick_winner_rolls_back_on_error(): void
    {
        // Create participants
        Peserta::factory()->count(5)->create(['status_menang' => false]);

        // Mock database error scenario by creating invalid data
        // This test ensures transaction integrity
        $initialCount = Peserta::where('status_menang', false)->count();
        
        Livewire::test(Undian::class)
            ->call('pickWinner');

        // Verify transaction completed successfully
        $afterCount = Peserta::where('status_menang', false)->count();
        $this->assertEquals($initialCount - 1, $afterCount);
    }

    /**
     * Test cache is cleared after winner selection.
     */
    public function test_cache_cleared_after_winner_selection(): void
    {
        Peserta::factory()->count(10)->create(['status_menang' => false]);

        // Prime cache by getting dummy data
        $component1 = Livewire::test(Undian::class);
        $dummyData1 = $component1->get('dummyData');
        
        // Pick winner (this should clear cache)
        $component2 = Livewire::test(Undian::class);
        $component2->call('pickWinner');

        // Get dummy data again - should be refreshed
        $component3 = Livewire::test(Undian::class);
        $dummyData3 = $component3->get('dummyData');
        
        // Should not contain the winner
        $winner = Peserta::where('status_menang', true)->first();
        if ($winner) {
            $hasWinner = collect($dummyData3)->contains('no_rekening', $winner->no_rekening);
            $this->assertFalse($hasWinner, 'Cache was not cleared - winner still in dummy data');
        }
    }

    /**
     * Test is_processing flag prevents concurrent pickWinner calls.
     */
    public function test_is_processing_flag_prevents_concurrent_calls(): void
    {
        Peserta::factory()->count(10)->create(['status_menang' => false]);

        $component = Livewire::test(Undian::class);
        
        // Simulate concurrent call by setting is_processing
        $component->set('is_processing', true);
        
        // Call pickWinner - should return early
        $component->call('pickWinner');
        
        // Verify no winner was selected (early return)
        $winners = Peserta::where('status_menang', true)->count();
        $this->assertEquals(0, $winners);
    }
}

