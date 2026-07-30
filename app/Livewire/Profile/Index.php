<?php

namespace App\Livewire\Profile;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use TallStackUi\Traits\Interactions;

#[Lazy]
#[Title('Profile')]
class Index extends Component
{
    use Interactions;
    use WithFileUploads;

    public $profileTmp;

    #[Locked]
    public $user;

    #[Url]
    public $tab = 'home';

    function mount()
    {
        $this->user = Auth::user();
    }

    #[On('updated-karywan')]
    public function refreshProfile()
    {
        // Triggers re-render and re-evaluates the computed 'karyawan' property.
    }


    #[Computed]
    public function karyawan()
    {
        return Karyawan::findOrFail($this->user->karyawan_id);
    }

    #[Computed]
    public function sisaCuti(): int
    {
        return $this->karyawan->sisa_cuti;
    }

    #[Computed]
    public function avatarUrl(): ?string
    {
        return $this->karyawan->foto
            ? route('api.users.avatar', ['userId' => $this->user->id])
            : null;
    }

    #[Computed]
    public function avatarInitials(): string
    {
        $name = trim($this->karyawan->full_nama ?: $this->karyawan->nama ?: '');

        if ($name === '') {
            return 'NA';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_substr($part, 0, 1);
        }

        return strtoupper($initials ?: 'NA');
    }

    function updateAvatar()
    {
        $this->validate([
            'profileTmp' => 'required|image|max:500', // 500kb Max
        ]);

        DB::beginTransaction();
        try {
            $path = $this->profileTmp->store('user/profile', 'public');

            Karyawan::where('id', $this->user->karyawan_id)
                ->update(['foto' => $path]);

            // Clear cache for updated avatar image & navbar data
            app(\App\Http\Controllers\ProfileImageCacheController::class)->clearCache($this->user->id);
            \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $this->user->id);

            $this->toast()
                ->success('Berhasil !', 'Profile foto berhasil diupdate.')
                ->send();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Gagal Update Profile !', 'Error : ' . $e->getMessage())
                ->send();
        }

        $this->reset('profileTmp');
    }

    public function render()
    {
        return view('livewire.profile.index')
            ->title('Profile');
    }
}
