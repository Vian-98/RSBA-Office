<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Sdm\AbsensiImportLog;

use Livewire\Attributes\Lazy;

#[Lazy]
#[Title('Kontrol Absensi')]
class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        abort_unless(
            auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk mengakses Halaman Kontrol Absensi.'
        );
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-4">
            <div class="flex justify-between items-center mb-6">
                <div class="h-8 bg-slate-200 rounded w-1/4"></div>
                <div class="h-10 bg-slate-200 rounded w-1/5"></div>
            </div>
            <div class="h-64 bg-slate-100 rounded-xl border border-slate-200"></div>
        </div>
        HTML;
    }

    public function render()
    {
        $logs = AbsensiImportLog::orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.kepegawaian.absensi.index', [
            'logs' => $logs
        ]);
    }
}
