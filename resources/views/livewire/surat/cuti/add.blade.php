<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-2" x-data="{
        tglCuti: @entangle('form.tgl_cuti'),
        lamaCuti: @entangle('form.lama_cuti'),
        jenisCuti: @entangle('form.jenis_cuti'),
        updateLamaCuti(value) {
            this.tglCuti = value;
            this.lamaCuti = this.tglCuti.length;
        }
    }">
        <div class="flex w-full flex-col gap-2">
            <span class="text-lg font-semibold text-primary-500">{{ $karyawan?->nama }}</span>

            <div class="flex w-full flex-col sm:flex-row gap-2">
                <x-ts:badge outline class="w-full sm:w-1/2 justify-center">
                    hari sisa cuti
                    <x-slot:left>
                        <p class="mr-2 text-xl" wire:loading.class="animate-pulse opacity-10" wire:target='form.jenis_cuti'>
                            {{ $form->sisa_cuti >= 999 ? '-' : $form->sisa_cuti }}</p>
                    </x-slot:left>
                </x-ts:badge>

                <x-ts:badge color="red" outline class="w-full sm:w-1/2 justify-center">
                    hari pengajuan cuti
                    <x-slot:left>
                        <p class="mr-2 text-xl" x-text="parseInt(jenisCuti) === 3 ? 90 : lamaCuti"></p>
                    </x-slot:left>
                </x-ts:badge>
            </div>


            @error('form.lama_cuti')
                <span class="text-danger-500">{{ $message }}</span>
            @enderror

            {{-- pilih karyawan --}}
            <x-ts:select.styled searchable placeholder="Cari Karyawan" :request="route('api.karyawan.ref')" select="label:nama|value:id" x-on:select="$wire.updateKaryawan($event.detail.select.id)" />

            <x-ts:select.styled wire:model='form.jenis_cuti' :disabled="$karyawan?->sisa_cuti < 0" wire:key="{{ $karyawan?->id }}" placeholder="Jenis Cuti" :options="$form->options_urgensi"
                select="label:label|value:value" x-on:select="$wire.set('form.jenis_cuti',$event.detail.select.value)">
            </x-ts:select.styled>

            @if((int) $form->jenis_cuti === 3)
                <x-ts:date format="YYYY-MM-DD" wire:model="form.tgl_cuti" :min-date="now()->subDays(-1)" placeholder="Pilih Tanggal Mulai Cuti" wire:key="cuti-melahirkan-start"
                    hint="Pilih tanggal mulai cuti melahirkan (otomatis diajukan selama 90 hari ke depan).">
                </x-ts:date>
            @else
                <x-ts:date multiple format="YYYY-MM-DD" wire:model='form.tgl_cuti' x-on:select="updateLamaCuti($event.detail.date)" :min-date="now()->subDays(-1)" placeholder="Tgl Cuti" wire:key="cuti-multiple"
                    hint="Pilih satu per satu tanggal cuti yang diajukan.">
                </x-ts:date>
            @endif

            <x-ts:textarea wire:model='form.keterangan' placeholder="Keterangan" />

            <x-ts:textarea wire:model='form.alamat' placeholder="Alamat selama Cuti" />

            <div wire:key="{{ $karyawan?->id }}">
                <x-ts:select.styled wire:key="atasan-select" multiple :limit="2" searchable grouped wire:model.defer="form.atasan" placeholder="Persetujuan Atasan" :request="route('api.karyawan.atasan.approver', [$karyawan?->jabatan?->first()?->id, 'karyawan_id' => $karyawan?->id])"
                    select="label:label|value:id" lazy="10" />
            </div>

        </div>
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button sm outline x-on:click="$dispatch('close-modal',{id:'create-cuti'})">Tutup</x-ts:button>
            <x-ts:button sm type="submit" icon="tabler.checks" loading="submit">Ajukan</x-ts:button>

        </div>
    </form>
</div>
