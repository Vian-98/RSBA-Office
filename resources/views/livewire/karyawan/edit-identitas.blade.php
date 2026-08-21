<div class="w-full">
    <form wire:submit.prevent="update" class="space-y-2">
        @csrf
        <div class="flex flex-col gap-2 lg:flex-row">
            <div class="w-full lg:w-1/4">
                <x-ts:input wire:model.lazy="form.nip" placeholder="NIP [Auto Generate]" readonly />
            </div>
            <div class="w-full lg:w-1/4">
                <x-ts:date wire:model.lazy='form.tgl_masuk' placeholder="Tgl. Masuk" />
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
                <x-ts:select.styled label="Status Pernikahan *" wire:model.lazy='form.status_pernikahan' placeholder="Pilih status pernikahan" :options="$pernikahan_options" select="label:label|value:value" />
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

        {{-- SATUSEHAT & LISENSI MEDIS --}}
        <div class="space-y-2 pt-2">
            <hr class="text-gray-200">
            <div class="flex items-center justify-between cursor-pointer select-none" wire:click="toggleLisensiSection">
                <span class="text-primary-500 flex items-center gap-1 font-semibold">
                    <x-ts:icon name="tabler.shield-bolt" class="h-5 w-5" />
                    Lisensi & Identitas Profesional (SATUSEHAT)
                </span>
                <span class="text-xs text-gray-500 hover:text-primary-600 font-medium flex items-center gap-1">
                    {{ $showLisensiSection ? 'Sembunyikan' : 'Tampilkan' }}
                    <x-ts:icon name="{{ $showLisensiSection ? 'tabler.chevron-up' : 'tabler.chevron-down' }}" class="h-4 w-4" />
                </span>
            </div>
        </div>

        @if($showLisensiSection)
            <div class="space-y-3 bg-gray-50/60 p-3 rounded-lg border border-gray-100">
                <div class="flex flex-col gap-2 lg:flex-row">
                    <div class="w-full lg:w-1/2">
                        <x-ts:input wire:model.lazy='form.ihs_number' placeholder="IHS Number (Kemenkes)" hint="Practitioner IHS Number dari Master Nakes Index Kemenkes RI" />
                    </div>
                </div>

                {{-- Data STR (Sesuai Format SDMK / Konsil Kemenkes) --}}
                <div class="border-t border-gray-200 pt-2 space-y-2">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider block">Data STR (Konsil / SDMK)</span>
                    <div class="flex flex-col gap-2 lg:flex-row">
                        <div class="w-full lg:w-1/2">
                            <x-ts:input wire:model.lazy='form.no_str' placeholder="Nomor STR" />
                        </div>
                        <div class="w-full lg:w-1/2">
                            <x-ts:input wire:model.lazy='form.jenis_str' placeholder="Jenis STR (e.g. STR Seumur Hidup, STR Berjangka)" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 lg:flex-row">
                        <div class="w-full lg:w-1/4">
                            <x-ts:date wire:model.lazy='form.str_terbit' placeholder="Tgl. Terbit STR" />
                        </div>
                        <div class="w-full lg:w-1/4">
                            <x-ts:date wire:model.lazy='form.str_berakhir' placeholder="Tgl. Berakhir STR" />
                        </div>
                        <div class="w-full lg:w-1/4">
                            <x-ts:input wire:model.lazy='form.jenis_profesi' placeholder="Jenis Profesi (e.g. Dokter, Perawat)" />
                        </div>
                        <div class="w-full lg:w-1/4">
                            <x-ts:input wire:model.lazy='form.kompetensi' placeholder="Kompetensi / Spesialisasi" />
                        </div>
                    </div>

                    {{-- Upload Softcopy STR --}}
                    <div class="rounded-lg bg-white p-3 border border-gray-200 space-y-2 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <x-ts:icon name="tabler.file-certificate" class="h-4 w-4 text-primary-600" />
                                Softcopy Dokumen STR (PDF / JPG / PNG, Max 5MB)
                            </span>
                            @if($current_str_doc)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    <x-ts:icon name="tabler.circle-check" class="h-3.5 w-3.5" /> File Ter-upload
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    <x-ts:icon name="tabler.alert-circle" class="h-3.5 w-3.5" /> Belum ada file
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="w-full flex-1">
                                <input type="file" wire:model="file_str" accept=".pdf,.png,.jpg,.jpeg" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer" />
                                @error('file_str') <span class="text-xs text-rose-500 font-semibold mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            @if($current_str_doc)
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ asset('storage/' . $current_str_doc->filename) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-md border border-blue-200 hover:bg-blue-100 transition-colors">
                                        <x-ts:icon name="tabler.eye" class="h-3.5 w-3.5" /> Lihat File
                                    </a>
                                    <button type="button" wire:click="deleteStrDoc" wire:confirm="Yakin ingin menghapus softcopy dokumen STR ini?" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 rounded-md border border-rose-200 hover:bg-rose-100 transition-colors">
                                        <x-ts:icon name="tabler.trash" class="h-3.5 w-3.5" /> Hapus
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Data SIP --}}
                <div class="border-t border-gray-200 pt-2 space-y-2">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider block">Data SIP</span>
                    <div class="flex flex-col gap-2 lg:flex-row">
                        <div class="w-full lg:w-1/2">
                            <x-ts:input wire:model.lazy='form.no_sip' placeholder="Nomor SIP (Surat Izin Praktik)" />
                        </div>
                        <div class="w-full lg:w-1/4">
                            <x-ts:date wire:model.lazy='form.sip_berakhir' placeholder="Tgl. Kadaluarsa SIP" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
