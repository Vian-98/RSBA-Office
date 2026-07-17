<div class="w-full">
    <form wire:submit.prevent='update' class="space-y-2">

        <div class="grid-row grid gap-2 lg:grid-cols-3">
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.status" searchable placeholder="Status Pegawai" :options="$status_options" select="label:label|value:value" />
                @if ($form->status != $status_init)
                    <div class="w-full">
                        <x-ts:date wire:model.lazy='form.tgl_status' placeholder="Tgl Status Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.jabatan" searchable placeholder="Jabatan" :options="$jabatan_options" select="label:nama|value:id" />
                @if ($form->jabatan != $jabatan_init)
                    <div class="w-full">
                        <x-ts:date wire:model.lazy='form.tgl_jabatan' placeholder="Tanggal Jabatan Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.ruangan" placeholder="Ruangan" :request="route('api.ruangan')" select="label:nama|value:id" />
                @if ($form->ruangan != $ruangan_init)
                    <div class="w-full">
                        <x-ts:date wire:model.lazy='form.tgl_ruangan' placeholder="Tanggal Ruangan Baru" />
                    </div>
                @endif
            </div>
            
            <div class="w-full">
                <x-ts:select.styled wire:model.defer="form.kategori_kerja" placeholder="Kategori Kerja" :options="[
                    ['label' => 'Pekerja Reguler (Jam Kantor)', 'value' => 'reguler'],
                    ['label' => 'Pekerja Shift', 'value' => 'shift']
                ]" select="label:label|value:value" />
            </div>
            
            <div class="w-full">
                <x-ts:select.styled wire:model.defer="form.pendidikan_setara" placeholder="Pendidikan Setara (Acuan Golongan)" :options="$pendidikan_options" select="label:label|value:value" />
                <span class="text-[10px] text-slate-400 mt-1 block">Default saat ini: <strong class="text-indigo-600 font-semibold">{{ $auto_pendidikan_label }}</strong> (Otomatis)</span>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button loading="update" xs outline icon="tabler.briefcase" type="submit">Update</x-ts:button>
        </div>
    </form>
</div>
