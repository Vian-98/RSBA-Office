<div class="w-full space-y-6">
    {{-- Floating Toast Notifications Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.notification-toast')

    {{-- Header & Tab Bar Navigation --}}
    <div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-2.5 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <button 
                wire:click="setTab('list')" 
                class="px-6 py-3 rounded-xl text-sm font-bold transition-all flex items-center space-x-2.5 {{ $activeTab === 'list' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Daftar Surat Ter-sign</span>
            </button>
            <button 
                wire:click="setTab('upload')" 
                class="px-6 py-3 rounded-xl text-sm font-bold transition-all flex items-center space-x-2.5 {{ $activeTab === 'upload' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-600 hover:bg-slate-100' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                <span>Upload & Sign Surat PDF</span>
            </button>
        </div>
    </div>

    {{-- Render Subcomponents with Lazy Load --}}
    <div class="w-full">
        @if ($activeTab === 'list')
            <livewire:kepegawaian.digital-signature.table lazy />
        @elseif ($activeTab === 'upload')
            <livewire:kepegawaian.digital-signature.form lazy />
        @endif
    </div>
</div>
