<?php

namespace App\Livewire\Auth;

use Throwable;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use TallStackUi\Traits\Interactions;

#[Title('Registrasi')]
#[Layout(('components.layouts.guest'))]
#[Lazy]
class Register extends Component
{
    use Interactions;

    public int $karyawan_id;
    public string $email, $password, $passwordConfirmation;

    public function rules(): array
    {
        return [
            'karyawan_id' => 'required|numeric|unique:users,karyawan_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|same:passwordConfirmation',
        ];
    }

    public function register()
    {
        $this->validate();
        // TODO: Impelement rate limiter
        // Create a new user
        try {
            $user = User::create([
                'email' => $this->email,
                'karyawan_id' => $this->karyawan_id,
                'password' => Hash::make($this->password),
            ]);

            // Backfill user_id di penugasan koordinator yang sudah ada jika karyawan_id cocok
            \Illuminate\Support\Facades\DB::table('sdm_ruangan_koordinator')
                ->where('karyawan_id', $user->karyawan_id)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id, 'updated_at' => now()]);

            Auth::login($user, true);

            $user->refresh();
            $user->load(['karyawan', 'karyawan.jabatan']);

            $user->assignRole('Guest');
            $user->syncRoleFromJabatan();

            // Fallback role sync jika syncRoleFromJabatan menetapkan Guest namun user adalah Dokter / Koordinator
            if ($user->hasRole('Guest')) {
                if ($user->isDokter() && $user->isKoordinator()) {
                    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Koordinator-Dokter']);
                    $user->syncRoles(['Koordinator-Dokter']);
                } elseif ($user->isKoordinator()) {
                    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Koordinator']);
                    $user->syncRoles(['Koordinator']);
                }
            }

            // Invalidate sidebar permissions cache
            cache()->forget('user-permissions:view:' . $user->id);

            $this->toast()
                ->success('Selamat Bergabung!', $user->karyawan?->nama ?? 'User')
                ->flash()
                ->send();

            return $this->redirect(route('profile.index'), navigate: true);
        } catch (Throwable $e) {
            $this->toast()
                ->error('Registrasi gagal!', 'Terjadi kesalahan saat mendaftar, error: ' . $e->getMessage())
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.auth.register');
    }
}
