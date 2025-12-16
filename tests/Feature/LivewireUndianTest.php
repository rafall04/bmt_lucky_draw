<?php

namespace Tests\Feature;

use App\Livewire\Undian;
use App\Models\Peserta;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireUndianTest extends TestCase
{

    /**
     * Test Undian component can be rendered.
     */
    public function test_undian_component_can_be_rendered(): void
    {
        Peserta::factory()->count(10)->create();

        Livewire::test(Undian::class)
            ->assertSuccessful()
            ->assertViewIs('livewire.undian');
    }

    /**
     * Test dummy data is loaded from eligible participants.
     */
    public function test_dummy_data_is_loaded_from_eligible_participants(): void
    {
        Peserta::factory()->count(20)->create(['status_menang' => false]);
        Peserta::factory()->count(5)->create(['status_menang' => true]); // Winners should not appear

        $component = Livewire::test(Undian::class);
        $dummyData = $component->get('dummyData');

        $this->assertNotEmpty($dummyData);
        $this->assertLessThanOrEqual(50, count($dummyData)); // Max 50 items

        // Verify winners are not in dummy data
        $winnerNoRekening = Peserta::where('status_menang', true)->pluck('no_rekening')->toArray();
        foreach ($dummyData as $item) {
            $this->assertNotContains($item['no_rekening'], $winnerNoRekening);
        }
    }

    /**
     * Test dummy data is cached.
     */
    public function test_dummy_data_is_cached(): void
    {
        Peserta::factory()->count(10)->create();

        $component1 = Livewire::test(Undian::class);
        $dummyData1 = $component1->get('dummyData');

        $component2 = Livewire::test(Undian::class);
        $dummyData2 = $component2->get('dummyData');

        // Cached data should be the same
        $this->assertEquals($dummyData1, $dummyData2);
    }

    /**
     * Test component handles settings correctly.
     */
    public function test_component_handles_settings_correctly(): void
    {
        // Create settings
        Setting::create([
            'key' => 'undian_title_text',
            'value' => 'TEST TITLE',
        ]);

        $component = Livewire::test(Undian::class);

        // Component should render without errors
        $component->assertSuccessful();
        $component->assertViewIs('livewire.undian');
    }

    /**
     * Test component initializes with correct defaults.
     */
    public function test_component_initializes_with_correct_defaults(): void
    {
        Livewire::test(Undian::class)
            ->assertSet('pemenang', null)
            ->assertSet('is_rolling', false)
            ->assertSet('hadiah_selected', '')
            ->assertSet('is_processing', false);
    }

    /**
     * Test component state changes correctly.
     */
    public function test_component_state_changes_correctly(): void
    {
        Peserta::factory()->count(5)->create();

        $component = Livewire::test(Undian::class)
            ->assertSet('is_rolling', false)
            ->call('startRolling')
            ->assertSet('is_rolling', true)
            ->assertSet('pemenang', null);
    }

    /**
     * Test component handles empty participants gracefully.
     */
    public function test_component_handles_empty_participants_gracefully(): void
    {
        // No participants created

        $component = Livewire::test(Undian::class);
        $dummyData = $component->get('dummyData');

        // Should return empty array, not crash
        $this->assertIsArray($dummyData);
    }

    /**
     * Test component renders with layout.
     */
    public function test_component_renders_with_layout(): void
    {
        Peserta::factory()->count(5)->create();

        $component = Livewire::test(Undian::class);

        // Component should render successfully with correct view
        $component->assertSuccessful();
        $component->assertViewIs('livewire.undian');
    }
}

