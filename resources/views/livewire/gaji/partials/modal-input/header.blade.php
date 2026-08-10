<!-- Employee Summary Header -->
<div class="bg-indigo-50/50 border border-indigo-100/75 rounded-2xl p-4 flex flex-col md:flex-row justify-between gap-4">
    <div class="space-y-1">
        <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Identitas Karyawan</span>
        <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $selectedKaryawan->full_nama }}</h3>
        <p class="text-xs text-slate-500">NIP: {{ $selectedKaryawan->nip }} | Status: {{ $selectedKaryawan->status->nama() }}</p>
    </div>
    <div class="md:text-right space-y-1 text-slate-600 text-xs">
        <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block">Kualifikasi</span>
        <p>Jabatan: <b class="text-slate-700">{{ $selectedKaryawan->jabatan->first()?->nama ?? '-' }}</b></p>
        <p>Masa Kerja: <b class="text-slate-700">{{ $selectedKaryawan->masakerja }}</b></p>
    </div>
</div>

@if($carriedOverFromPeriode)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-2xs flex items-start gap-3">
        <x-tabler-alert-circle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
        <div>
            <span class="font-bold">Info Salin Data:</span> Data pada form ini otomatis disalin dari slip gaji periode <span class="font-bold">{{ \Carbon\Carbon::parse($carriedOverFromPeriode . '-01')->translatedFormat('F Y') }}</span>. Silakan periksa dan sesuaikan sebelum disimpan.
        </div>
    </div>
@endif
