<div>
    <form wire:submit.prevent="submit" class="space-y-2" autocomplete="off">
        @csrf
        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Status Pegawai *" wire:model.lazy='form.status' placeholder="Pilih status pegawai" :options="$status_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Kategori Kerja *" wire:model.lazy='form.kategori_kerja' placeholder="Pilih kategori kerja" :options="$kategori_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:date label="Tanggal Masuk *" wire:model.lazy='form.tgl_masuk' placeholder="Pilih tanggal masuk" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="NIP (otomatis)" wire:model.lazy="form.nip" placeholder="Dibuat otomatis saat disimpan" readonly />
            </div>
        </div>

        {{-- identitas --}}
        <div class="space-y-2 pt-2">
            <hr>
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.user" class="h-5 w-5" />
                Identitas
            </span>
        </div>
        <div class="flex w-full flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:input label="Nama Lengkap *" wire:model.lazy="form.nama" placeholder="Nama tanpa gelar" hint="Input nama tanpa gelar." />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Gelar Depan" wire:model.lazy="form.gelar_depan" placeholder="Opsional" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Gelar Belakang" wire:model.lazy="form.gelar_belakang" placeholder="Opsional" />
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input label="NIK *" wire:model.lazy='form.nik' placeholder="16 digit NIK" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="NPWP" wire:model.lazy='form.npwp' placeholder="Opsional" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Tempat Lahir" wire:model.lazy='form.tempat_lahir' placeholder="Opsional" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:date label="Tanggal Lahir *" wire:model.lazy='form.tgl_lahir' placeholder="Pilih tanggal lahir" />
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Agama *" wire:model.lazy='form.agama' placeholder="Pilih agama" :options="$agama_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Suku" wire:model.lazy='form.suku' placeholder="Opsional" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Jenis Kelamin" wire:model.lazy='form.jk' placeholder="Default: Laki-laki" :options="$jk_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Status Pernikahan *" wire:model.lazy='form.status_pernikahan' placeholder="Pilih status pernikahan" :options="$pernikahan_options" select="label:label|value:value" />
            </div>
        </div>

        {{-- KONTAK --}}
        <div class="space-y-2 pt-2">
            <hr>
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.phone-plus" class="h-5 w-5" />
                Kontak
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Nomor HP *" wire:model.lazy='form.hp' placeholder="Nomor HP utama" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input label="Nomor HP 2" wire:model.lazy='form.hp2' placeholder="Opsional" />
            </div>
        </div>

        {{-- ALAMAT --}}
        <div class="space-y-2 pt-2">
            <hr>
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.map-plus" class="h-5 w-5" />
                Alamat
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Provinsi *" wire:model.live.blur="form.prov" searchable :request="route('api.prov')" select="label:nama|value:kode" placeholder="Pilih provinsi" />
            </div>
            <div wire:key='{{ $form->prov }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Kabupaten *" wire:model.live.blur='form.kab' searchable :request="route('api.kab', ['id' => $form->prov])" select="label:nama|value:kode" placeholder="Pilih kabupaten" />
            </div>
            <div wire:key='{{ $form->kab }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Kecamatan *" wire:model.live.blur='form.kec' searchable :request="route('api.kec', ['id' => $form->kab])" select="label:nama|value:kode" placeholder="Pilih kecamatan" />
            </div>
            <div wire:key='{{ $form->kec }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Desa *" wire:model.live.blur='form.desa' searchable :request="route('api.desa', ['id' => $form->kec])" select="label:nama|value:kode" placeholder="Pilih desa" />
            </div>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:textarea label="Alamat KTP *" wire:model.live.blur='form.alamat' placeholder="Alamat sesuai KTP" />
            </div>
        </div>

        {{-- domisili --}}
        <div class="flex w-full flex-row lg:w-full">
            <x-ts:checkbox wire:click='domisili' label="Domisili Sama Dengan KTP ? " />
            <span wire:loading wire:target='domisili' class="text-indigo-500"> Wait...</span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled label="Provinsi Domisili" wire:model.live.blur="form.dom_prov" searchable :request="route('api.prov')" select="label:nama|value:kode" placeholder="Opsional" />
            </div>
            <div wire:key='{{ $form->dom_prov }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Kabupaten Domisili" wire:model.live.blur='form.dom_kab' searchable :request="route('api.kab', ['id' => $form->dom_prov])" select="label:nama|value:kode" placeholder="Opsional" />
            </div>
            <div wire:key='{{ $form->dom_kab }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Kecamatan Domisili" wire:model.live.blur='form.dom_kec' searchable :request="route('api.kec', ['id' => $form->dom_kab])" select="label:nama|value:kode" placeholder="Opsional" />
            </div>
            <div wire:key='{{ $form->dom_kec }}' class="w-full lg:w-1/4">
                <x-ts:select.styled label="Desa Domisili" wire:model.live.blur='form.dom_desa' searchable :request="route('api.desa', ['id' => $form->dom_kec])" select="label:nama|value:kode" placeholder="Opsional" />
            </div>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:textarea label="Alamat Domisili" wire:model.lazy='form.dom_alamat' placeholder="Opsional" />
            </div>
        </div>


        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'new-karyawan'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
