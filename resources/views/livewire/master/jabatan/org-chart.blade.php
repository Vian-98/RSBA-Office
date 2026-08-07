<div class="flex flex-col space-y-4">
    <!-- Toolbar Controls -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm border border-slate-100">
        <div class="flex items-center space-x-2">
            <div class="relative w-64">
                <input 
                    type="text" 
                    id="org-search-input"
                    placeholder="Cari Jabatan / Karyawan..." 
                    class="w-full rounded-lg border border-slate-200 py-1.5 pl-9 pr-3 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <span id="org-search-count" class="text-xs text-slate-500 hidden"></span>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" id="btn-org-zoom-in" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition-colors">Zoom In</button>
            <button type="button" id="btn-org-zoom-out" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition-colors">Zoom Out</button>
            <button type="button" id="btn-org-fit" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition-colors">Fit Screen</button>
            <button type="button" id="btn-org-expand" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-semibold hover:bg-emerald-100 transition-colors">Expand Semua</button>
            <button type="button" id="btn-org-collapse" class="px-3 py-1.5 bg-amber-50 text-amber-600 rounded-lg text-xs font-semibold hover:bg-amber-100 transition-colors">Kuncupkan Semua</button>
        </div>
    </div>

    @if (!empty($chartWarnings))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <div class="font-semibold">Struktur organisasi perlu diverifikasi</div>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($chartWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Container Org Chart -->
    <div class="relative min-h-[650px] w-full overflow-hidden rounded-xl bg-slate-900 shadow-inner border border-slate-800">
        <div wire:ignore id="bumbeishvili-org-chart" class="h-[650px] w-full"></div>
    </div>

    @script
    <script>
        (function() {
            const nodesData = @js($chartData);
            let chartInstance = null;

            function injectScript(src) {
                return new Promise((resolve, reject) => {
                    if (document.querySelector(`script[src='${src}']`)) {
                        resolve();
                        return;
                    }
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = () => resolve();
                    s.onerror = (e) => reject(e);
                    document.head.appendChild(s);
                });
            }

            async function initBumbeishviliChart() {
                try {
                    if (typeof d3 === 'undefined') {
                        await injectScript('https://d3js.org/d3.v7.min.js');
                    }
                    if (typeof d3?.flexTree === 'undefined') {
                        await injectScript('https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js');
                    }
                    if (typeof d3?.OrgChart === 'undefined') {
                        await injectScript('https://cdn.jsdelivr.net/npm/d3-org-chart@3');
                    }
                } catch (e) {
                    console.error('Gagal memuat library D3 OrgChart:', e);
                    const container = document.querySelector('#bumbeishvili-org-chart');
                    if (container) {
                        container.innerHTML = '<div class="flex h-full items-center justify-center px-6 text-center text-sm text-amber-200">Bagan tidak dapat dimuat. Periksa koneksi ke CDN D3 atau gunakan asset lokal.</div>';
                    }
                    return;
                }
                renderBumbeishviliChart();
            }

            function renderBumbeishviliChart() {
                const container = document.querySelector('#bumbeishvili-org-chart');
                if (!container || typeof d3 === 'undefined' || typeof d3.OrgChart === 'undefined') {
                    if (container) {
                        container.innerHTML = '<div class="flex h-full items-center justify-center px-6 text-center text-sm text-amber-200">Library D3 OrgChart belum tersedia.</div>';
                    }
                    return;
                }
                container.innerHTML = '';

                try {
                    chartInstance = new d3.OrgChart()
                        .container('#bumbeishvili-org-chart')
                        .data(nodesData)
                        .nodeWidth(d => 280)
                        .nodeHeight(d => 140)
                        .childrenMargin(d => 50)
                        .compactMarginBetween(d => 35)
                        .compactMarginPair(d => 30)
                        .nodeContent((d) => {
                            const isVacant = d.data.name.includes('Vacant') || d.data.name.includes('Belum Diisi');
                            const badgeColor = isVacant ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
                            const headerGradient = isVacant ? 'from-amber-600 to-amber-800' : 'from-indigo-600 to-purple-600';

                            return `
                                <div class="relative h-full w-full rounded-xl bg-slate-800 text-white shadow-xl border border-slate-700/80 overflow-hidden flex flex-col justify-between hover:border-indigo-500 transition-all duration-200">
                                    <div class="h-2 w-full bg-gradient-to-r ${headerGradient}"></div>
                                    <div class="p-3 flex items-start space-x-3">
                                        <div class="relative shrink-0">
                                            <img src="${d.data.avatar}" class="h-12 w-12 rounded-full object-cover border-2 border-indigo-400/50 shadow-md bg-slate-700" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[10px] font-medium tracking-wide uppercase text-indigo-400 truncate">${d.data.department}</div>
                                            <div class="text-sm font-bold text-slate-100 truncate" title="${d.data.name}">${d.data.name}</div>
                                            <div class="text-xs text-slate-300 truncate" title="${d.data.position}">${d.data.position}</div>
                                        </div>
                                    </div>
                                    <div class="px-3 pb-3 pt-1 flex items-center justify-between text-[11px] border-t border-slate-700/50 bg-slate-900/40">
                                        <span class="text-slate-400 font-mono text-[10px]">${d.data.nip !== '—' ? 'NIP: ' + d.data.nip : d.data.tingkat}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border ${badgeColor}">
                                            ${d.data.status}
                                        </span>
                                    </div>
                                </div>
                            `;
                        })
                        .render()
                        .fit();

                    setupButtons();
                } catch (e) {
                    console.error('Error rendering D3 OrgChart:', e);
                }
            }

            function setupButtons() {
                document.getElementById('btn-org-zoom-in')?.addEventListener('click', () => chartInstance?.zoomIn());
                document.getElementById('btn-org-zoom-out')?.addEventListener('click', () => chartInstance?.zoomOut());
                document.getElementById('btn-org-fit')?.addEventListener('click', () => chartInstance?.fit());
                document.getElementById('btn-org-expand')?.addEventListener('click', () => chartInstance?.expandAll());
                document.getElementById('btn-org-collapse')?.addEventListener('click', () => chartInstance?.collapseAll());

                const searchInput = document.getElementById('org-search-input');
                const searchCount = document.getElementById('org-search-count');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const val = e.target.value.trim().toLowerCase();
                        if (!chartInstance || !val) {
                            if (searchCount) searchCount.classList.add('hidden');
                            return;
                        }
                        const match = nodesData.find(n => 
                            (n.name && n.name.toLowerCase().includes(val)) ||
                            (n.position && n.position.toLowerCase().includes(val)) ||
                            (n.department && n.department.toLowerCase().includes(val))
                        );
                        if (match) {
                            if (searchCount) {
                                searchCount.textContent = '1 ditemukan';
                                searchCount.classList.remove('hidden');
                            }
                            chartInstance.setHighlighted(match.id).render();
                            chartInstance.setExpanded(match.id).render();
                        } else {
                            if (searchCount) searchCount.classList.add('hidden');
                        }
                    });
                }
            }

            setTimeout(initBumbeishviliChart, 100);
        })();
    </script>
    @endscript
</div>
