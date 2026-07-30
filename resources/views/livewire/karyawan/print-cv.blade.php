<div>
    @if ($karyawan)
    <body class="bg-gray-100">
        <div class="mx-auto my-8 max-w-6xl bg-white">
            <div class="flex flex-col md:flex-row">
                <!-- Left Column - Personal Info -->
                <div class="bg-secondary w-full p-8 md:w-1/3">
                    <div class="mb-8 text-center">
                        <div class="mx-auto mb-4 h-40 w-40 overflow-hidden rounded-full border-4 border-white">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80"
                                alt="Profile Photo" class="h-full w-full object-cover">
                        </div>
                        <h1 class="text-xl font-bold">{{ $karyawan->nama }}</h1>
                        {{-- @dd($karyawan->jabatan) --}}
                        <h2 class="text-lg text-primary-500">{{ $karyawan->jabatan[0]->nama ?? '-' }}</h2>
                    </div>

                    <div class="mb-8">
                        <h3 class="mb-4 border-b border-primary-300 pb-2 text-xl font-bold">Kontak</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <x-ts:icon name="tabler.phone" class="mr-3 w-5 flex-shrink-0" />
                                <span>{{ $karyawan->hp }}</span>
                            </li>
                            <li class="flex items-start">
                                <x-ts:icon name="tabler.mail" class="mr-3 w-5 flex-shrink-0" />
                                <span>{{ $karyawan->user?->email }}</span>
                            </li>
                            <li class="flex items-start">
                                <x-ts:icon name="tabler.map-2" class="mr-3 w-5 flex-shrink-0" />
                                <span>{{ $karyawan->alamat }}</span>
                            </li>
                            <li class="flex items-start">
                                <x-ts:icon name="tabler.map-2" class="mr-3 w-5 flex-shrink-0" />
                                <span>{{ $karyawan->dom_alamat }}</span>
                            </li>

                        </ul>
                    </div>

                    {{-- 
                    <div class="mb-8">
                        <h3 class="mb-4 border-b border-primary-300 pb-2 text-xl font-bold">Skill</h3>
                        <div class="space-y-4">

                            <div class="space-y-2">
                                <div>
                                    <div class="mb-1 flex justify-between">
                                        <span>JavaScript</span>
                                        <span>95%</span>
                                    </div>
                                    <div class="skill-bar">
                                        <div class="skill-level" style="width: 95%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 flex justify-between">
                                        <span>Python</span>
                                        <span>90%</span>
                                    </div>
                                    <div class="skill-bar">
                                        <div class="skill-level" style="width: 90%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 flex justify-between">
                                        <span>Java</span>
                                        <span>80%</span>
                                    </div>
                                    <div class="skill-bar">
                                        <div class="skill-level" style="width: 80%"></div>
                                    </div>
                                </div>
                            </div>


                            <div>
                                <h4 class="mb-1 font-semibold">Tools</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-indigo-500/50 px-3 py-1 text-sm">Git</span>
                                    <span class="rounded-full bg-indigo-500/50 px-3 py-1 text-sm">Docker</span>
                                    <span class="rounded-full bg-indigo-500/50 px-3 py-1 text-sm">AWS</span>
                                    <span class="rounded-full bg-indigo-500/50 px-3 py-1 text-sm">Agile</span>
                                </div>
                            </div>
                        </div>
                    </div> 
                    --}}
                </div>

                <!-- Right Column - Professional Info -->
                <div class="w-full p-8 text-gray-800 md:w-2/3">
                    {{-- <div class="mb-8">
                        <h2 class="section-title">Ringkasan Profile</h2>
                        <p class="text-gray-700">

                        </p>
                    </div> --}}

                    <div class="mb-8">
                        <h2 class="section-title">Pengalaman Kerja</h2>

                        @foreach ($karyawan->historyJabatan as $jabatan)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="flex flex-col md:flex-row md:justify-between">
                                    <h3 class="text-lg font-bold">{{ $jabatan->nama }}</h3>
                                    {{-- <span
                                        class="text-primary font-semibold">{{ Carbon\Carbon::parse($jabatan->pivot->tgl_mulai)->translatedFormat('M Y') . ' - ' . Carbon\Carbon::parse($jabatan->pivot->tgl_berakhir)->translatedFormat('M Y') ?? 'Sekarang' }}
                                    </span> --}}
                                </div>
                                <h4 class="text-gray-700">
                                    {{ Carbon\Carbon::parse($jabatan->pivot->tgl_mulai)->translatedFormat('M Y') . ' - ' . Carbon\Carbon::parse($jabatan->pivot->tgl_berakhir)->translatedFormat('M Y') ?? 'Sekarang' }}
                                </h4>
                                {{-- <h4 class="text-secondary mb-2 font-semibold">Tech Solutions Inc., San Francisco</h4>
                            <ul class="list-disc space-y-1 pl-5 text-gray-700">
                                <li>Led a team of 5 developers in building a SaaS platform serving 10,000+ monthly active users</li>
                                <li>Architected microservices backend using Node.js and Python, improving performance by 40%</li>
                                <li>Implemented CI/CD pipelines reducing deployment times from 2 hours to 15 minutes</li>
                                <li>Mentored junior developers through code reviews and pair programming</li>
                            </ul> --}}
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-8">
                        <h2 class="section-title">Pendidikan</h2>

                        @foreach ($this->pendidikans as $pendidikan)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="flex flex-col md:flex-row md:justify-between">
                                    <h3 class="text-lg font-bold">{{ $pendidikan->nama }}</h3>
                                    <span class="text-primary font-semibold">{{ Carbon\Carbon::parse($pendidikan->tahun_lulus)->translatedFormat('M Y') }}</span>
                                </div>
                                <h4 class="text-secondary mb-2 font-semibold">{{ $pendidikan->instansi }}</h4>
                                <p class="text-gray-700">Tingkat <b>{{ $pendidikan->tingkat->nama() }}</b> {{ $pendidikan?->gelar ? ', Gelar : ' . $pendidikan->gelar : '' }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-8">
                        <h2 class="section-title">Pelatihan Dan Sertifikasi</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            @foreach ($this->sertifikasi as $sertifikasi)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <h3 class="text-secondary font-bold">{{ $sertifikasi->nama }}</h3>
                                    {{-- <p class="text-sm text-gray-600">{{ $sertifikasi->instansi }}</p> --}}
                                    {{-- <p class="text-sm text-gray-600">{{ Carbon\Carbon::parse($sertifikasi->tgl_berlaku)->translatedFormat('M Y') }}</p> --}}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h2 class="section-title">Penghargaan</h2>
                        <div class="space-y-4">
                            {{-- <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <h3 class="text-secondary font-bold">Machine Learning API</h3>
                                <p class="mb-2 text-sm text-gray-600">TensorFlow, Flask, AWS | 2020</p>
                                <p class="text-gray-700">REST API for image classification deployed on AWS with auto-scaling capabilities.</p>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    @else
        <div class="p-8 text-center text-gray-400">
            Pilih karyawan terlebih dahulu.
        </div>
    @endif
</div>
