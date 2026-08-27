<div class="space-y-6">
    {{-- Header Banner & Action Button --}}
    @include('livewire.surat.arsip-surat.partials.header')

    {{-- Alert jika Docstore Offline --}}
    @include('livewire.surat.arsip-surat.partials.alert-docstore')

    {{-- Filter & Tab Kategori Arsip --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {{-- Navigation Tabs Kategori --}}
        @include('livewire.surat.arsip-surat.partials.navigation-tabs')

        {{-- Form Pencarian & Range Filter --}}
        @include('livewire.surat.arsip-surat.partials.search-filter')

        {{-- Grid Card / Tabel Arsip Surat --}}
        @include('livewire.surat.arsip-surat.partials.document-list')
    </div>

    {{-- MODAL TAMBAH JENIS ARSIP BARU --}}
    @include('livewire.surat.arsip-surat.partials.modal-kategori')

    {{-- DRAWER / MODAL DETAIL DOKUMEN DOCSTORE --}}
    @include('livewire.surat.arsip-surat.partials.modal-detail')
</div>
