<!-- Quick Navigation / Module Info -->
<div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
    <h3 class="text-lg font-bold text-slate-800">Modul Kerja Anda</h3>
    <p class="text-sm text-slate-500 mt-1">Gunakan tautan cepat di bawah ini untuk mengakses fitur utama sesuai tugas Anda.</p>
    
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mt-6">
        @if($userRoleName === 'Super-Admin' || $userRoleName === 'Staff-SDM')
            <a href="{{ route('kepegawaian.karyawan.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition-all text-center">
                <span class="rounded-lg bg-indigo-50 p-2.5 text-indigo-600 mb-2">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-700">Data Karyawan</span>
            </a>
        @endif

        @if($userRoleName === 'Super-Admin' || $userRoleName === 'Bagian-Umum')
            <a href="{{ route('umum.master.barang') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-teal-100 hover:bg-teal-50/30 transition-all text-center">
                <span class="rounded-lg bg-teal-50 p-2.5 text-teal-600 mb-2">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-700">Manajemen Stok</span>
            </a>
        @endif

        @if($userRoleName === 'Super-Admin' || $userRoleName === 'Keuangan')
            <a href="{{ route('keuangan.hutang.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-rose-100 hover:bg-rose-50/30 transition-all text-center">
                <span class="rounded-lg bg-rose-50 p-2.5 text-rose-600 mb-2">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-1.971-.659-1.171-.88-1.171-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0M9 6.241h.008v.008H9V6.241Zm0 11.518h.008v.008H9v-.008Z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-700">Buku Hutang</span>
            </a>
        @endif

        <a href="{{ route('dashboard.display-monitor.admin') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-amber-100 hover:bg-amber-50/30 transition-all text-center">
            <span class="rounded-lg bg-amber-50 p-2.5 text-amber-600 mb-2">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </span>
            <span class="text-xs font-semibold text-slate-700">Display Monitor Admin</span>
        </a>

        <a href="{{ route('dashboard.poli.admin') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-violet-100 hover:bg-violet-50/30 transition-all text-center">
            <span class="rounded-lg bg-violet-50 p-2.5 text-violet-600 mb-2">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
            <span class="text-xs font-semibold text-slate-700">Poli Admin</span>
        </a>
    </div>
</div>
