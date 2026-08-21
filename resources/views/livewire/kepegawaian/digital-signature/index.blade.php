<div class="w-full space-y-6">
    {{-- Floating Toast Notifications Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.notification-toast')

    {{-- Navigation Tabs Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 gap-4 pb-1">
        <div class="flex space-x-1 sm:space-x-2 overflow-x-auto pb-2 sm:pb-0">
            <button wire:click="setTab('create')" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center space-x-2 whitespace-nowrap cursor-pointer
                    {{ $activeTab === 'create' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Studio Upload & Sign</span>
            </button>

            <button wire:click="setTab('my_submissions')" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center space-x-2 whitespace-nowrap cursor-pointer
                    {{ $activeTab === 'my_submissions' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Status Pengajuan Saya</span>
            </button>

            <button wire:click="setTab('pending_approvals')" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center space-x-2 whitespace-nowrap cursor-pointer relative
                    {{ $activeTab === 'pending_approvals' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Perlu Persetujuan Saya</span>
            </button>
        </div>
    </div>

    {{-- Tab Contents --}}
    <div class="w-full">
        @if ($activeTab === 'create')
            <livewire:kepegawaian.digital-signature.form :key="'form-'.($revisionDocId ?? 'new')" :rejectedDocId="$revisionDocId" />
        @elseif ($activeTab === 'my_submissions')
            <livewire:kepegawaian.digital-signature.my-submissions-table />
        @elseif ($activeTab === 'pending_approvals')
            <livewire:kepegawaian.digital-signature.pending-approvals-table />
        @endif
    </div>
</div>
