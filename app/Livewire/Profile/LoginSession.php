<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use TallStackUi\Traits\Interactions;

class LoginSession extends Component
{
    use Interactions;

    public $sessions = [];

    public function mount()
    {
        $this->loadSessions();
    }

    public function loadSessions()
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();

        // Get sessions from database if exists
        $dbSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        $list = [];

        foreach ($dbSessions as $sess) {
            $userAgent = $sess->user_agent;
            
            $browser = $this->getBrowser($userAgent);
            $os = $this->getOS($userAgent);

            $list[] = [
                'id' => $sess->id,
                'ip_address' => $sess->ip_address,
                'is_current' => ($sess->id === $currentSessionId),
                'browser' => $browser,
                'os' => $os,
                'last_activity' => \Carbon\Carbon::createFromTimestamp($sess->last_activity)->diffForHumans(),
            ];
        }

        // Fallback for file driver (standard local dev)
        if (empty($list)) {
            $userAgent = request()->header('User-Agent');
            $list[] = [
                'id' => $currentSessionId,
                'ip_address' => request()->ip(),
                'is_current' => true,
                'browser' => $this->getBrowser($userAgent),
                'os' => $this->getOS($userAgent),
                'last_activity' => 'Sedang Aktif',
            ];
        }

        $this->sessions = $list;
    }

    public function logoutOtherDevices()
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();

        // Clear all other sessions in database
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        $this->loadSessions();

        $this->toast()
            ->success('Berhasil!', 'Sesi login di perangkat lain telah dinonaktifkan.')
            ->send();
    }

    private function getBrowser($userAgent)
    {
        if (empty($userAgent)) return 'Unknown Browser';

        if (preg_match('/chrome/i', $userAgent)) {
            if (preg_match('/edg/i', $userAgent)) return 'Microsoft Edge';
            if (preg_match('/opr/i', $userAgent)) return 'Opera';
            return 'Google Chrome';
        }
        if (preg_match('/firefox/i', $userAgent)) return 'Mozilla Firefox';
        if (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) return 'Apple Safari';
        if (preg_match('/msie/i', $userAgent) || preg_match('/trident/i', $userAgent)) return 'Internet Explorer';

        return 'Browser';
    }

    private function getOS($userAgent)
    {
        if (empty($userAgent)) return 'Unknown OS';

        if (preg_match('/windows|win32/i', $userAgent)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'macOS';
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) return 'iOS';
        if (preg_match('/android/i', $userAgent)) return 'Android';

        return 'Sistem Operasi';
    }

    public function render()
    {
        return view('livewire.profile.login-session');
    }
}
