<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
    {{-- Header & Search Bar Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.table-header')

    <div class="overflow-x-auto w-full">
        <table class="w-full text-left text-sm text-slate-600">
            {{-- Column Headers: DOKUMEN | DITANDATANGANI OLEH | STATUS | AKSI --}}
            @include('livewire.kepegawaian.digital-signature.partials.table-head')

            <tbody class="divide-y divide-slate-100">
                @forelse ($documents as $doc)
                    {{-- Reusable Document Row Partial --}}
                    @include('livewire.kepegawaian.digital-signature.partials.table-row', ['doc' => $doc])
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                            Belum ada dokumen PDF ter-sign. Silakan pindah ke tab <strong>Upload & Sign Surat PDF</strong> untuk membuat dokumen pertama Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($documents && is_object($documents) && method_exists($documents, 'links'))
        <div class="p-4 border-t border-slate-100 w-full">
            {{ $documents->links() }}
        </div>
    @endif

    {{-- Document Metadata Modal Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.metadata-modal')
</div>
