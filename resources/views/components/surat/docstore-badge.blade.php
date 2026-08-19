@props([
    'version' => 1,
    'docstoreKey' => '',
])

<div style="
    background: linear-gradient(135deg, #064e3b, #065f46);
    color: #d1fae5;
    border-radius: 6px;
    padding: 6px 12px;
    margin-bottom: 12px;
    font-size: 10px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.05em;
" class="no-print">
    🔒 DATA TERVERIFIKASI DARI BANK SURAT (DOCSTORE) — Versi: v{{ $version }}
    &nbsp;|&nbsp; Key: {{ $docstoreKey }}
</div>
