<div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-100/80 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl bg-white p-12 text-center shadow-2xl">
        <div class="p-8">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="mb-4 text-2xl font-bold text-red-600">Sistem Sedang Opname Stok</h1>
            <p class="text-gray-700">Semua transaksi sementara tidak dapat diakses.</p>
            <p class="mt-4 text-sm text-gray-500">Silahkan coba lagi nanti.</p>

            <div class="mt-8 flex justify-center">
                <a href="javascript:history.back()" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200">
                    <x-ts:icon name="tabler.arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
