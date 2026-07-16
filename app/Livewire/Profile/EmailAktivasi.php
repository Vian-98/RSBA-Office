<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Mail\EmailVerificationMail;
use TallStackUi\Traits\Interactions;

class EmailAktivasi extends Component
{
    use Interactions;

    public $email;
    public $isVerified;
    public $verifiedAt;

    // View states: 'view', 'change_email', 'verify_code'
    public $viewState = 'view';
    
    // Inputs
    public $new_email;
    public $verification_code;

    public function mount()
    {
        $this->loadStatus();
    }

    public function loadStatus()
    {
        $user = Auth::user()->fresh();
        $this->email = $user->email;
        $this->isVerified = !is_null($user->email_verified_at);
        $this->verifiedAt = $user->email_verified_at ? \Carbon\Carbon::parse($user->email_verified_at)->translatedFormat('d F Y H:i') : null;
    }

    public function showChangeEmail()
    {
        $this->new_email = $this->email;
        $this->viewState = 'change_email';
    }

    public function saveNewEmail()
    {
        $user = Auth::user();
        
        $this->validate([
            'new_email' => 'required|email|unique:users,email,' . $user->id,
        ], [
            'new_email.required' => 'Email baru harus diisi.',
            'new_email.email' => 'Format email tidak valid.',
            'new_email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'email' => $this->new_email,
                'email_verified_at' => null
            ]);

        \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $user->id);

        $this->loadStatus();
        $this->viewState = 'view';

        $this->toast()
            ->success('Email Diubah!', 'Alamat email berhasil diperbarui. Silakan aktivasi email baru Anda.')
            ->send();
    }

    public function cancelChangeEmail()
    {
        $this->viewState = 'view';
        $this->reset('new_email');
    }

    public function triggerActivation()
    {
        $user = Auth::user();
        
        $code = rand(100000, 999999);
        Cache::put('email_verification_code_' . $user->id, $code, now()->addMinutes(15));

        $name = $user->karyawan->full_nama ?? $user->email;

        try {
            Mail::to($this->email)->send(new EmailVerificationMail($code, $name));
            
            $this->viewState = 'verify_code';
            
            $this->toast()
                ->success('Tautan Dikirim!', 'Kode verifikasi telah dikirim ke email ' . $this->email)
                ->send();
        } catch (\Throwable $e) {
            $this->toast()
                ->error('Gagal Mengirim Email', 'Gagal mengirim email verifikasi: ' . $e->getMessage())
                ->send();
        }
    }

    public function submitVerificationCode()
    {
        $user = Auth::user();
        $cachedCode = Cache::get('email_verification_code_' . $user->id);

        $this->validate([
            'verification_code' => 'required|numeric',
        ], [
            'verification_code.required' => 'Kode verifikasi harus diisi.',
            'verification_code.numeric' => 'Kode harus berupa angka.',
        ]);

        if ($this->verification_code == $cachedCode) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['email_verified_at' => now()]);

            Cache::forget('email_verification_code_' . $user->id);
            \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $user->id);

            $this->loadStatus();
            $this->viewState = 'view';
            $this->reset('verification_code');

            $this->toast()
                ->success('Aktivasi Berhasil!', 'Email Anda telah berhasil diverifikasi.')
                ->send();
        } else {
            $this->addError('verification_code', 'Kode verifikasi salah atau sudah kedaluwarsa.');
        }
    }

    public function cancelVerification()
    {
        $this->viewState = 'view';
        $this->reset('verification_code');
    }

    public function cancelActivation()
    {
        $user = Auth::user();
        
        DB::table('users')
            ->where('id', $user->id)
            ->update(['email_verified_at' => null]);

        \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $user->id);

        $this->loadStatus();

        $this->toast()
            ->success('Reset Berhasil!', 'Status aktivasi email telah di-reset.')
            ->send();
    }

    public function render()
    {
        return view('livewire.profile.email-aktivasi');
    }
}
