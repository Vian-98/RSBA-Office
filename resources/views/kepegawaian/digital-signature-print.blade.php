<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Dokumen Ter-Sign - {{ $document->title }}</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        /* === Toolbar (Hidden when printing) === */
        .toolbar {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #334155;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .toolbar-left { display: flex; align-items: center; gap: 16px; }
        .btn-close {
            padding: 8px 12px;
            background: #334155;
            color: #cbd5e1;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .btn-close:hover { background: #475569; }
        .toolbar-title { font-size: 14px; font-weight: 700; color: #fff; }
        .toolbar-sub { font-size: 11px; color: #94a3b8; font-family: monospace; }
        .btn-print {
            padding: 10px 22px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
            transition: all 0.15s;
        }
        .btn-print:hover { background: linear-gradient(135deg, #4338ca, #3730a3); }

        /* === Canvas Wrapper === */
        .canvas-wrapper {
            flex: 1;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            overflow: auto;
        }
        .page-container {
            position: relative;
            background: white;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            border-radius: 4px;
            overflow: hidden;
            width: 850px;
            max-width: 100%;
        }
        .pdf-canvas {
            display: block;
            width: 100%;
            height: auto;
        }

        .seal-stamp {
            position: absolute;
            background: rgba(255, 255, 255, 0.98);
            padding: 10px 12px;
            border-radius: 12px;
            border: 2px solid #059669;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25), 0 0 0 3px rgba(16, 185, 129, 0.2);
            text-align: left;
            width: 230px;
            z-index: 40;
            pointer-events: none;
        }
        .seal-header {
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid #d1fae5;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .seal-icon {
            width: 15px; height: 15px;
            background: #059669;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 9px;
            font-weight: 900;
            flex-shrink: 0;
        }
        .seal-label {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            color: #065f46;
            letter-spacing: 0.04em;
        }
        .seal-body {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .seal-qr-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 3px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .seal-meta {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .seal-name { font-size: 11px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .seal-date { font-size: 8.5px; color: #64748b; font-family: monospace; }
        .seal-hash {
            font-size: 7.5px;
            font-family: monospace;
            color: #4338ca;
            background: #eef2ff;
            padding: 2px 4px;
            border-radius: 4px;
            border: 1px solid #e0e7ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .seal-valid-badge {
            font-size: 7px;
            color: #059669;
            font-weight: 700;
        }

        /* === Media Print === */
        @media print {
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
            }
            .toolbar { display: none !important; }
            .canvas-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
                display: block !important;
                overflow: visible !important;
            }
            .page-container {
                position: relative !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                page-break-after: always;
                page-break-inside: avoid;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .pdf-canvas {
                display: block !important;
                width: 100% !important;
                height: auto !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <header class="toolbar">
        <div class="toolbar-left">
            <button type="button" class="btn-close" onclick="window.close()">
                <span>✕</span>
                <span>Tutup</span>
            </button>
            <div>
                <div class="toolbar-title">{{ $document->title }}</div>
                <div class="toolbar-sub">ID Docstore: {{ $document->docstore_key }} | {{ $document->document_number }}</div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('digital-signature.download', $document->id) }}" class="btn-print" style="background: linear-gradient(135deg, #059669, #047857); text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Unduh PDF Resmi (.pdf)</span>
            </a>
            <button type="button" class="btn-print" onclick="window.print()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Dokumen (Print)</span>
            </button>
        </div>
    </header>

    {{-- Printable metadata text for Chrome PDF Printer stream parsing --}}
    <div style="position: absolute; bottom: 1px; left: 1px; font-size: 1px; color: rgba(0,0,0,0.01); pointer-events: none; z-index: -1;">
        OFFICIAL_SEALED_DOCUMENT {{ $document->document_number }} {{ $document->docstore_key }}
    </div>

    <main class="canvas-wrapper" id="canvasWrapper">
        <div id="loadingState" style="color: #94a3b8; padding: 40px; font-size: 14px; text-align: center;">
            <p>Memuat dan Merekonstruksi Stempel Dokumen PDF...</p>
        </div>
    </main>

    <script>
        const pdfBase64 = @json($pdfBase64);
        const stampX = @json($stampX);
        const stampY = @json($stampY);
        const stampScale = @json($stampScale);
        const qrBase64 = @json($qrBase64 ?? null);
        const userName = @json(optional($document->user)->name ?? 'Super Admin');
        const signDate = @json($document->created_at->format('d M Y H:i'));
        const byteHash = @json(substr($document->byte_counter_hash, 0, 14));

        document.addEventListener('DOMContentLoaded', async function() {
            try {
                // Convert base64 string to Uint8Array (required by PDF.js v3+)
                const binaryString = atob(pdfBase64);
                const len = binaryString.length;
                const bytes = new Uint8Array(len);
                for (let i = 0; i < len; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }

                const loadingTask = pdfjsLib.getDocument({ data: bytes });
                const pdf = await loadingTask.promise;
                
                const wrapper = document.getElementById('canvasWrapper');
                wrapper.innerHTML = '';

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    const page = await pdf.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 2.0 });

                    const pageDiv = document.createElement('div');
                    pageDiv.className = 'page-container';

                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-canvas';
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    pageDiv.appendChild(canvas);

                    // Note: Hard-stamped PDF files already contain the RSBA QR Seal directly inside Page 1 PDF stream.
                    // If stamp overlay is enabled for fallback:
                    if (pageNum === 1 && (stampX === -1)) {
                        const stampDiv = document.createElement('div');
                        stampDiv.className = 'seal-stamp';
                        stampDiv.style.left = stampX + '%';
                        stampDiv.style.top = stampY + '%';
                        stampDiv.style.transform = `scale(${stampScale / 100})`;
                        stampDiv.style.transformOrigin = 'top left';

                        const qrImgHtml = qrBase64 
                            ? `<img src="data:image/png;base64,${qrBase64}" alt="QR" style="width:56px;height:56px;display:block;">`
                            : `<div style="width:56px;height:56px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:8px;">QR</div>`;

                        stampDiv.innerHTML = `
                            <div class="seal-header">
                                <div class="seal-icon">✓</div>
                                <span class="seal-label">E-SIGNATURE & VERIFIKASI RSBA</span>
                            </div>
                            <div class="seal-body">
                                <div class="seal-qr-box">
                                    ${qrImgHtml}
                                    <span style="font-size:6px;color:#64748b;font-weight:600;margin-top:1px;">Scan Verifikasi</span>
                                </div>
                                <div class="seal-meta">
                                    <div class="seal-name">${userName}</div>
                                    <div class="seal-date">${signDate} WIB</div>
                                    <div class="seal-valid-badge">Dokumen Sah Terdaftar</div>
                                </div>
                            </div>
                        `;
                        pageDiv.appendChild(stampDiv);
                    }

                    wrapper.appendChild(pageDiv);

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                }

                // Auto trigger print dialog once all pages are fully rendered into DOM
                setTimeout(function() {
                    window.print();
                }, 500);

            } catch (err) {
                console.error("Gagal merekonstruksi PDF dengan PDF.js:", err);
            }
        });
    </script>
</body>
</html>
