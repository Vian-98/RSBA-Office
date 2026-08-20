<!-- Status Banners -->
@if($periodStatus === 'approved')
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 flex items-center gap-3">
        <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-700">
            <x-tabler-lock class="h-5 w-5" />
        </div>
        <div class="text-xs font-medium">
            <span class="font-bold block text-emerald-900 mb-0.5">Periode Terkunci & Disetujui</span>
            Seluruh data slip gaji pada periode <b>{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</b> telah disetujui oleh manajemen dan terkunci. Data tidak dapat diedit atau ditambah.
        </div>
    </div>
@elseif($periodStatus === 'review_pajak')
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 flex items-center gap-3">
        <div class="rounded-lg bg-amber-500/10 p-2 text-amber-700">
            <x-tabler-eye-check class="h-5 w-5" />
        </div>
        <div class="text-xs font-medium">
            @can('approve-kepegawaian-gaji-pajak')
                <span class="font-bold block text-amber-900 mb-0.5">Menunggu Review Anda (Tim Pajak)</span>
                Silakan review data potongan pajak PPh 21 pada setiap slip. Setelah selesai, setujui melalui halaman <b>Rekap Bulanan</b>.
            @else
                <span class="font-bold block text-amber-900 mb-0.5">Sedang Direview Tim Pajak</span>
                Data gaji periode <b>{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</b> sedang dalam proses review oleh Tim Pajak. Data tidak dapat diedit.
            @endcan
        </div>
    </div>
@elseif($periodStatus === 'review_sdm')
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-800 flex items-center gap-3">
        <div class="rounded-lg bg-blue-500/10 p-2 text-blue-700">
            <x-tabler-clipboard-check class="h-5 w-5" />
        </div>
        <div class="text-xs font-medium">
            <span class="font-bold block text-blue-900 mb-0.5">Menunggu Finalisasi SDM</span>
            Review pajak selesai. Silakan lakukan finalisasi dan penerbitan SP3 melalui halaman <b>Rekap Bulanan</b>.
        </div>
    </div>
@endif
