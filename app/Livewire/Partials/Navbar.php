<?php

namespace App\Livewire\Partials;

use Throwable;
use Livewire\Component;
use App\Livewire\Auth\Login;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Cache;

#[Lazy]
#[Isolate]
class Navbar extends Component
{
    use Interactions;

    public $title;

    // navbar user data
    public $nama;
    public $email;
    public $foto;
    public $hasFoto;
    public $textFoto;
    public $colorFoto;
    public $isLoading = true;
    public bool $hasUnread = false;

    public function mount($title)
    {
        $this->title = $title;
        $this->loadUserData();
        $this->checkUnreadNotifications();
    }


    function loadUserData()
    {
        $this->isLoading = true;

        $userId = Auth::id();
        $cacheKey = "navbar-user:{$userId}";
        $cacheExp = 60 * 60; //60 menit

        $datas = Cache::remember($cacheKey, $cacheExp, function () {
            $User = Auth::user();
            $karyawan = $User->karyawan;

            return [
                'nama' => $karyawan->nama,
                'email' => $User->email,
                // 'foto' => $karyawan->foto ? asset('storage/' . $karyawan->foto) : null,
                // 'foto' => $karyawan->foto ?? null,
                'foto' => $karyawan->foto ? route('api.users.avatar', ['userId' => $User->id]) : null,
                'has_foto' => !empty($karyawan->foto),
                'text_foto' => $this->getInitials($karyawan->nama),
                'color' => $karyawan->jk === 'L' ? 'indigo' : 'rose'
            ];
        });

        $this->nama = $datas['nama'];
        $this->email = $datas['email'];
        $this->foto = $datas['foto'];
        $this->hasFoto = $datas['has_foto'];
        $this->textFoto = $datas['text_foto'];
        $this->colorFoto = $datas['color'];

        $this->isLoading = false;
    }


    // Initial Nama Karyawan
    function getInitials($name, $length = 2)
    {
        if (empty(trim($name))) return '';

        $words = preg_split('/\s+/', trim($name));
        $initials = '';

        for ($i = 0; $i < min($length, count($words)); $i++) {
            $initials .= substr($words[$i], 0, 1);
        }

        return strtoupper($initials);
    }

    function logout()
    {
        try {
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return $this->redirect(Login::class, navigate: true);
        } catch (Throwable $e) {
            $this->toast()
                ->error("An error occured {$e}")
                ->send();
        }
    }

    public function checkUnreadNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            $this->hasUnread = false;
            return;
        }

        $readNotifs = $user->read_notifications ?? [];

        // 1. Welcome notif
        if (!in_array('welcome', $readNotifs)) {
            $this->hasUnread = true;
            return;
        }

        // 2. Cuti approvals
        try {
            if ($user->hasRole('Super-Admin') || $user->hasRole('Staff-SDM')) {
                // Pending cuti needing approval
                $pendingCutis = \App\Models\Surat\SuratCuti::whereIn('status', [\App\Enums\StatusApproval::PENDING, \App\Enums\StatusApproval::WAITING])
                    ->latest()
                    ->take(5)
                    ->pluck('id')
                    ->map(fn($id) => 'cuti-pending-' . $id)
                    ->toArray();

                foreach ($pendingCutis as $cid) {
                    if (!in_array($cid, $readNotifs)) {
                        $this->hasUnread = true;
                        return;
                    }
                }
            } else {
                $myCutis = \App\Models\Surat\SuratCuti::where('karyawan_id', $user->karyawan_id)
                    ->latest()
                    ->take(5)
                    ->pluck('id')
                    ->map(fn($id) => 'cuti-status-' . $id)
                    ->toArray();

                foreach ($myCutis as $cid) {
                    if (!in_array($cid, $readNotifs)) {
                        $this->hasUnread = true;
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Maintenance
        try {
            if ($user->hasRole('Super-Admin') || $user->hasRole('Bagian-Umum')) {
                $maintenance = \App\Models\Maintenance\Jadwal::latest()
                    ->take(5)
                    ->pluck('id')
                    ->map(fn($id) => 'maint-' . $id)
                    ->toArray();

                foreach ($maintenance as $mid) {
                    if (!in_array($mid, $readNotifs)) {
                        $this->hasUnread = true;
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {}

        $this->hasUnread = false;
    }

    #[On('notification-updated')]
    public function refreshUnreadStatus()
    {
        $this->checkUnreadNotifications();
    }

    public function placeholder()
    {
        return <<<HTML
            <div class="flex w-full h-16 px-6 items-center rounded-md">
                    <div class="flex flex-row gap-2 animated-pulse">
                            <div class="h-6 w-8 bg-neutral-300 rounded-md"></div>
                            <div class="w-28 flex flex-col space-y-1">
                                <div class="h-3 w-44 bg-neutral-300 rounded-full"></div>
                                <div class="h-2 w-28 bg-neutral-300 rounded-full"></div>           
                            </div>
                    </div>
                    <div class="ml-auto flex animated-pulse">
                         <div class="flex items-center gap-3">
                            <div class="flex animate-pulse space-x-4">
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-4 w-20 rounded bg-gray-300"></div>
                                    <div class="h-3 w-16 rounded bg-gray-300"></div>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-gray-300"></div>
                            </div>
                        </div>
                    </div>
            </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
