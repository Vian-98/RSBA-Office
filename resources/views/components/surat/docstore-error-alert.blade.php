@props([
    'error' => 'Data surat tidak dapat dimuat dari bank surat (docstore).',
    'docstoreKey' => null,
])

<div style="
    background: linear-gradient(135deg, #fef2f2, #fff0f0);
    border: 1px solid #fca5a5;
    border-radius: 8px;
    padding: 20px 24px;
    margin-bottom: 20px;
    font-family: Arial, sans-serif;
">
    <div style="display: flex; align-items: flex-start; gap: 12px;">
        <svg style="width:24px;height:24px;color:#dc2626;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p style="font-weight: bold; color: #991b1b; margin: 0 0 6px 0; font-size: 14px;">
                Surat Tidak Dapat Dicetak
            </p>
            <p style="color: #7f1d1d; margin: 0; font-size: 13px; line-height: 1.5;">
                {{ $error }}
            </p>
            @if (empty($docstoreKey))
                <p style="color: #b91c1c; margin: 8px 0 0 0; font-size: 11px;">
                    ℹ️ Pastikan proses approval telah dilakukan. Surat akan otomatis tersinkronisasi ke bank surat setelah disetujui.
                </p>
            @else
                <p style="color: #b91c1c; margin: 8px 0 0 0; font-size: 11px;">
                    ℹ️ Pastikan server bank surat (docstore) aktif di <code>{{ env('DOCSTORE_API_URL', 'http://localhost:8000/api') }}</code>
                </p>
            @endif
        </div>
    </div>
</div>
