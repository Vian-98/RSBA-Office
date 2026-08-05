<div class="w-full">
    <form wire:submit.prevent='update' class="space-y-2">

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.status" searchable placeholder="Status Pegawai" :options="$status_options" select="label:label|value:value" />
                @if ($form->status != $status_init)
                    <div class="w-full mt-1">
                        <x-ts:date wire:model.lazy='form.tgl_status' placeholder="Tgl Status Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.kategori_kerja" placeholder="Kategori Kerja" :options="$kategori_options" select="label:label|value:value" />
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.jabatan" searchable placeholder="Jabatan" :options="$jabatan_options" select="label:nama|value:id" />
                @if ($form->jabatan != $jabatan_init)
                    <div class="w-full mt-1">
                        <x-ts:date wire:model.lazy='form.tgl_jabatan' placeholder="Tanggal Penugasan Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.bagian" searchable placeholder="Bagian / Departemen" :options="$bagian_options" select="label:nama|value:id" />
                @if ($form->bagian != $bagian_init && $form->jabatan == $jabatan_init)
                    <div class="w-full mt-1">
                        <x-ts:date wire:model.lazy='form.tgl_jabatan' placeholder="Tanggal Penugasan Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.ruangan" placeholder="Ruangan Utama" :request="route('api.ruangan')" select="label:nama|value:id" />
                @if ($form->ruangan != $ruangan_init)
                    <div class="w-full mt-1">
                        <x-ts:date wire:model.lazy='form.tgl_ruangan' placeholder="Tanggal Ruangan Baru" />
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button loading="update" xs outline icon="tabler.briefcase" type="submit">Update</x-ts:button>
        </div>
    </form>

    <hr class="my-6 border-slate-200" />

    {{-- Section Riwayat Penugasan Ruangan & Jabatan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        
        {{-- Tabel Riwayat Penugasan Ruangan --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    🏢 Riwayat Penugasan Ruangan
                </h3>
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">
                    {{ $form->karyawan?->historyRuangan->count() ?? 0 }} Record
                </span>
            </div>

            @if($form->karyawan?->historyRuangan->isEmpty())
                <div class="text-center py-6 text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">
                    Belum ada riwayat penugasan ruangan.
                </div>
            @else
                <div class="overflow-x-auto border border-slate-100 rounded-lg">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2">Ruangan</th>
                                <th class="px-3 py-2">Tgl Mulai</th>
                                <th class="px-3 py-2">Tgl Selesai</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($form->karyawan->historyRuangan as $histR)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-2 font-semibold text-slate-800">
                                        {{ $histR->nama }}
                                        @if($histR->pivot->is_utama)
                                            <span class="text-[10px] text-emerald-600 font-bold ml-1">(Utama)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histR->pivot->tgl_mulai ? date('d/m/Y', strtotime($histR->pivot->tgl_mulai)) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histR->pivot->tgl_berakhir ? date('d/m/Y', strtotime($histR->pivot->tgl_berakhir)) : 'Sekarang' }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if(is_null($histR->pivot->tgl_berakhir))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                Riwayat
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tabel Riwayat Jabatan --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    👔 Riwayat Mutasi Jabatan
                </h3>
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">
                    {{ $form->karyawan?->historyJabatan->count() ?? 0 }} Record
                </span>
            </div>

            @if($form->karyawan?->historyJabatan->isEmpty())
                <div class="text-center py-6 text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">
                    Belum ada riwayat mutasi jabatan.
                </div>
            @else
                <div class="overflow-x-auto border border-slate-100 rounded-lg">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2">Jabatan</th>
                                <th class="px-3 py-2">Bagian</th>
                                <th class="px-3 py-2">Tgl Mulai</th>
                                <th class="px-3 py-2">Tgl Selesai</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($form->karyawan->historyJabatan as $histJ)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-2 font-semibold text-slate-800">
                                        {{ $histJ->nama }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ \App\Models\Sdm\Bagian::find($histJ->pivot->bagian_id ?? $histJ->bagian_id)?->nama ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histJ->pivot->tgl_mulai ? date('d/m/Y', strtotime($histJ->pivot->tgl_mulai)) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histJ->pivot->tgl_berakhir ? date('d/m/Y', strtotime($histJ->pivot->tgl_berakhir)) : 'Sekarang' }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if(is_null($histJ->pivot->tgl_berakhir))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                Riwayat
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>
