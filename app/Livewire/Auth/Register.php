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

            Auth::login($user, true);

            $user->assignRole('Guest');

            $this->toast()
                ->success('Selamat Bergabung!', Auth::user()->karyawan->nama)
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
