<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Sdm\AbsensiImportLog;

#[Title('Kontrol Absensi')]
class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        abort_unless(
            auth()->user()?->hasRole('Super-Admin') || auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk mengakses Halaman Kontrol Absensi.'
        );
    }

    public function render()
    {
        $logs = AbsensiImportLog::orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.kepegawaian.absensi.index', [
            'logs' => $logs
        ]);
    }
}
