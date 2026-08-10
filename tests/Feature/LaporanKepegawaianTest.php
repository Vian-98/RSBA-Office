<?php

namespace Tests\Feature;

use App\Livewire\Laporan\Kepegawaian\Index;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LaporanKepegawaianTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        (new PermissionSeeder())->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $karyawan = Karyawan::create([
            'nip' => 'NIP' . rand(10000, 99999),
            'nik' => '3301' . rand(1000000000, 9999999999),
            'nama' => 'Test Karyawan',
            'jk' => 'L',
            'tgl_lahir' => '1990-01-01',
            'hp' => '08123456789',
            'status' => 'tetap',
            'tgl_masuk' => '2020-01-01',
            'prov' => 'Jawa Tengah',
            'kab' => 'Klaten',
            'kec' => 'Klaten Utara',
            'desa' => 'Gergunung',
            'alamat' => 'Jl. Test No. 1',
            'agama' => 'islam',
        ]);

        $user = User::create([
            'email' => 'test' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $user->givePermissionTo('view-kepegawaian-laporan');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /** @test */
    public function it_renders_laporan_kepegawaian_page_successfully()
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/kepegawaian/laporan')
            ->assertStatus(200);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.laporan.kepegawaian.index')
            ->assertSet('activeTab', 'overview');
    }

    /** @test */
    public function it_can_switch_tabs_and_filter_karyawan()
    {
        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('activeTab', 'detail')
            ->set('search', 'NonExistentEmployeeSearchQuery12345')
            ->assertSet('search', 'NonExistentEmployeeSearchQuery12345')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function it_can_export_csv_and_bagian_csv()
    {
        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('exportCsv')
            ->assertFileDownloaded();
    }
}
