<div class="w-full">
    <form wire:submit.prevent="update" class="space-y-2">
        @csrf
        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy="form.nip" placeholder="NIP [Auto Generate]" readonly />
            </div>
            <div class="w-full lg:w-1/4">
                @if($canEditTglMasuk)
                    <x-ts:date wire:model.lazy='form.tgl_masuk' placeholder="Tgl. Masuk" />
                @else
                    <x-ts:input wire:model.lazy='form.tgl_masuk' placeholder="Tgl. Masuk" disabled readonly class="bg-gray-100 cursor-not-allowed" />
                @endif
            </div>
        </div>

        {{-- identitas --}}
        <div class="flex w-full flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy="form.nama" placeholder="Nama Lengkap" hint="Input nama tanpa gelar." />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy="form.gelar_depan" placeholder="Gelar Depan" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy="form.gelar_belakang" placeholder="Gelar Belakang" />
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.nik' placeholder="NIK" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.npwp' placeholder="NPWP" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.tempat_lahir' placeholder="Tempat Lahir" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:date wire:model.lazy='form.tgl_lahir' placeholder="Tgl. Lahir" />
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.lazy='form.agama' placeholder="Pilih Agama" :options="$agama_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.suku' placeholder="Suku" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.lazy='form.jk' placeholder="Kelamin" :options="$jk_options" select="label:label|value:value" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.lazy='form.status_pernikahan' placeholder="Status Pernikahan" :options="$pernikahan_options" select="label:label|value:value" />
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:select.styled wire:model.lazy='form.ptkp_status' placeholder="Status PTKP (Pajak PPh 21)" :options="$ptkp_options" select="label:label|value:value" />
            </div>
        </div>

        {{-- BPJS --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.shield-check" class="h-5 w-5" />
                BPJS
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.bpjs_kesehatan' placeholder="Nomor BPJS Kesehatan" />
            </div>
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.bpjs_tk' placeholder="Nomor BPJS Ketenagakerjaan (TK)" />
            </div>
        </div>

        {{-- REKENING BANK --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.building-bank" class="h-5 w-5" />
                Rekening Pembayaran Gaji
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.nama_bank' placeholder="Nama Bank (misal: BSI, Mandiri, BCA)" />
            </div>
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.no_rekening' placeholder="Nomor Rekening Bank" />
            </div>
        </div>

        {{-- BPJS --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.shield-check" class="h-5 w-5" />
                BPJS
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.bpjs_kesehatan' placeholder="Nomor BPJS Kesehatan" />
            </div>
            <div class="w-full lg:w-1/2">
                <x-ts:input wire:model.lazy='form.bpjs_tk' placeholder="Nomor BPJS Ketenagakerjaan (TK)" />
            </div>
        </div>

        {{-- KONTAK --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.phone-plus" class="h-5 w-5" />
                Kontak
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.hp' placeholder="HP" />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy='form.hp2' placeholder="HP [2]" />
            </div>
        </div>

        {{-- ALAMAT --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <span class="text-primary-500 flex gap-1 font-semibold">
                <x-ts:icon name="tabler.map-plus" class="h-5 w-5" />
                Alamat
            </span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur="form.prov" searchable :request="route('api.prov')" select="label:nama|value:kode" placeholder="Provinsi" />
            </div>
            <div wire:key='{{ $form->prov }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.kab' searchable :request="route('api.kab', ['id' => $form->prov])" select="label:nama|value:kode" placeholder="Kabupaten" />
            </div>
            <div wire:key='{{ $form->kab }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.kec' searchable :request="route('api.kec', ['id' => $form->kab])" select="label:nama|value:kode" placeholder="Kecamatan" />
            </div>
            <div wire:key='{{ $form->kec }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.desa' searchable :request="route('api.desa', ['id' => $form->kec])" select="label:nama|value:kode" placeholder="Desa" />
            </div>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:textarea wire:model.live.blur='form.alamat' placeholder="Alamat" />
            </div>
        </div>

        {{-- domisili --}}
        <div class="flex w-full flex-row lg:w-full">
            <x-ts:checkbox wire:click='domisili' label="Domisili Sama Dengan Alamat ? " />
            <span wire:loading wire:target='domisili' class="text-indigo-500"> Wait...</span>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur="form.dom_prov" searchable :request="route('api.prov')" select="label:nama|value:kode" placeholder="Provinsi" />
            </div>
            <div wire:key='{{ $form->dom_prov }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.dom_kab' searchable :request="route('api.kab', ['id' => $form->dom_prov])" select="label:nama|value:kode" placeholder="Kabupaten" />
            </div>
            <div wire:key='{{ $form->dom_kab }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.dom_kec' searchable :request="route('api.kec', ['id' => $form->dom_kab])" select="label:nama|value:kode" placeholder="Kecamatan" />
            </div>
            <div wire:key='{{ $form->dom_kec }}' class="w-full lg:w-1/4">
                <x-ts:select.styled wire:model.live.blur='form.dom_desa' searchable :request="route('api.desa', ['id' => $form->dom_kec])" select="label:nama|value:kode" placeholder="Desa" />
            </div>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/2">
                <x-ts:textarea wire:model.lazy='form.dom_alamat' placeholder="Alamat Domisili" />
            </div>
        </div>


        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button loading="update" xs outline icon="tabler.user-edit" type="submit">Update</x-ts:button>
        </div>
    </form>
</div>
