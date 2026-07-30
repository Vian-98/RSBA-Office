<div class="flex flex-col gap-4">

    <div class="flex flex-col rounded-lg bg-indigo-50/50 border border-indigo-100 p-4">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Role Anda</span>
        <div class="flex flex-wrap gap-2">
            @forelse ($role as $r)
                <span class="inline-flex items-center rounded-full bg-indigo-100/50 px-2.5 py-0.5 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">
                    {{ $r }}
                </span>
            @empty
                <span class="text-xs italic text-gray-400">Belum memiliki role.</span>
            @endforelse
        </div>
    </div>

    @if($role->contains('Super-Admin'))
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/20 p-4 text-xs text-indigo-800 flex items-start gap-3">
            <x-tabler-info-circle class="h-5 w-5 text-indigo-600 shrink-0 mt-0.5" />
            <div>
                <span class="font-bold text-indigo-900 block mb-0.5">Bypass Hak Akses Aktif</span>
                Akun Anda memiliki role <strong>Super-Admin</strong>. Sistem memberikan hak akses penuh ke seluruh modul aplikasi (Gate Bypass) secara otomatis, sehingga daftar permission di database tidak perlu didefinisikan secara eksplisit.
            </div>
        </div>
    @endif

    <span class="flex flex-row gap-4">
        <div class="flex w-1/2 flex-col gap-2 rounded-lg bg-gray-50 p-4">
            <span class="font-semibold text-indigo-500">Permisson Default Role</span>
            <ul class="ms-4">
                @forelse ($permissionInRole as $permission)
                    <li>{{ $permission->name }}</li>
                @empty
                    <li class="text-sm italic text-gray-400">Tidak memiliki permission default.</li>
                @endforelse

            </ul>
        </div>

        <div class="flex w-1/2 flex-col gap-2 rounded-lg bg-gray-50 p-4">
            <span class="font-semibold text-indigo-500">Spesial Permission User</span>
            <ul class="ms-4">
                @forelse ($specialPermission as $item)
                    <li>{{ $item->name }} </li>
                @empty
                    <li class="text-sm italic text-gray-400">Tidak memiliki permission khusus.</li>
                @endforelse
            </ul>
        </div>
    </span>
</div>
