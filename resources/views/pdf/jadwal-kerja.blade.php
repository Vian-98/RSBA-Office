<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>JADWAL {{ strtoupper($jadwalKerja->ruangan->nama ?? '') }} - {{ $jadwalKerja->bulan }}/{{ $jadwalKerja->tahun }}</title>
    <style>
        @page {
            margin: 4mm 5mm 4mm 5mm;
            size: a4 landscape;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000000 !important;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header-title {
            text-align: center;
            margin-bottom: 6px;
        }

        .header-logo {
            height: 42px;
            width: auto;
            margin-bottom: 3px;
        }

        .header-title h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            color: #002060 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title h2 {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 1px 0 0 0;
            text-transform: uppercase;
            color: #000000 !important;
        }

        .header-title h3 {
            font-size: 8.5pt;
            font-weight: bold;
            margin: 1px 0 0 0;
            text-transform: uppercase;
            color: #000000 !important;
        }

        /* Matrix Table - table-layout: auto for DomPDF column sizing */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: auto;
        }

        .schedule-table th, .schedule-table td {
            border: 1px solid #000000;
            text-align: center;
            vertical-align: middle;
            padding: 2px 0px;
            font-size: 6.5pt;
            height: 14px;
            color: #000000 !important;
        }

        .schedule-table th {
            background-color: #ffffff;
            font-weight: bold;
            color: #000000 !important;
        }

        .schedule-table th.sunday-header {
            background-color: #ff0000 !important;
            color: #ffffff !important;
        }

        .schedule-table td.sunday-cell {
            background-color: #ffffff;
        }

        .text-left {
            text-align: left !important;
            padding-left: 3pt !important;
        }

        /* Keterangan Footer Layout */
        .ket-section {
            width: 100%;
            margin-top: 3px;
            color: #000000 !important;
        }

        .ket-title {
            font-size: 7.5pt;
            font-weight: bold;
            margin-bottom: 2px;
            color: #000000 !important;
        }

        .ket-text {
            font-size: 6.5pt;
            margin-bottom: 3px;
            line-height: 1.2;
            color: #000000 !important;
        }

        /* Sub Tables */
        .sub-table {
            border-collapse: collapse;
            font-size: 6.5pt;
            color: #000000 !important;
        }

        .sub-table th, .sub-table td {
            border: 1px solid #000000;
            padding: 2px 4px;
            color: #000000 !important;
        }

        .sub-table th {
            font-weight: bold;
            background-color: #ffffff;
            text-align: center;
        }

        /* Signatures */
        .sig-container {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 7.5pt;
            color: #000000 !important;
        }

        .sig-box {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
            color: #000000 !important;
        }

        .sig-space {
            height: 40px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000000 !important;
        }
    </style>
</head>
<body>

    @php
        // Helper functions 100% matched with UI (kelola.blade.php)
        if (!function_exists('getDayLetter')) {
            function getDayLetter(Carbon\Carbon $date) {
                $map = ['M', 'S', 'S', 'R', 'K', 'J', 'S'];
                return $map[$date->dayOfWeek];
            }
        }

        if (!function_exists('labelSingkat')) {
            function labelSingkat($kode, $nama) {
                $kode = strtoupper(trim($kode));
                if ($kode === 'REGULER') return 'REG';
                if (in_array($kode, ['PAGI', 'SIANG', 'MALAM'])) return $kode;

                $parts = explode(' ', trim($nama), 3);
                $waktu  = $parts[0] ?? '';
                $second = $parts[1] ?? '';
                $w = strtoupper(mb_substr($waktu, 0, 1));

                if (strtolower($second) === 'awal') {
                    $third = $parts[2] ?? '';
                    $room = strtoupper(mb_substr(explode(' ', $third)[0] ?? '', 0, 3));
                    return $w . 'A' . ($room ? '.' . $room : '');
                }
                if (!empty($second)) {
                    return $w . '.' . strtoupper(mb_substr($second, 0, 3));
                }
                return strtoupper(mb_substr($kode, 0, 5));
            }
        }

        if (!function_exists('autoWarna')) {
            function autoWarna($kode, $warna) {
                if ($warna && $warna !== '#e2e8f0') return $warna;
                $kode = strtoupper($kode);
                if ($kode === 'REGULER') return '#66BB6A';
                if (str_starts_with($kode, 'P'))  return '#42A5F5';
                if (str_starts_with($kode, 'S'))  return '#FFA726';
                if (str_starts_with($kode, 'M'))  return '#AB47BC';
                return '#78909C';
            }
        }

        if (!function_exists('teksCerahGelap')) {
            function teksCerahGelap($hex) {
                $hex = ltrim($hex, '#');
                if (strlen($hex) < 6) return '#1e293b';
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                return $lum > 0.55 ? '#1e293b' : '#ffffff';
            }
        }
    @endphp

    {{-- Header RS dengan Logo RSBA --}}
    <div class="header-title">
        @if(!empty($logoSrc))
            <img src="{{ $logoSrc }}" class="header-logo" alt="Logo RSBA" />
        @endif
        <h1>{{ strtoupper($namaPerusahaan) }}</h1>
        <h2>JADWAL {{ $jadwalKerja->isDokterSchedule() ? 'DOKTER' : 'KARYAWAN' }} {{ strtoupper($jadwalKerja->ruangan->nama ?? 'RUANGAN') }}</h2>
        <h3>BULAN {{ strtoupper(DateTime::createFromFormat('!m', $jadwalKerja->bulan)->format('F')) }} {{ $jadwalKerja->tahun }}</h3>
    </div>

    {{-- Matrix Table with DomPDF point widths --}}
    @php
        $numDays = count($dates);
        $dateColWidth = floor(460 / $numDays); // width in points per date column
    @endphp
    <table class="schedule-table">
        <colgroup>
            <col style="width: 14pt;">
            <col style="width: 95pt;">
            <col style="width: 65pt;">
            @foreach($dates as $date)
                <col style="width: {{ $dateColWidth }}pt;">
            @endforeach
        </colgroup>
        <thead>
            {{-- Row 1: Dates --}}
            <tr>
                <th rowspan="2" style="width: 14pt;">NO</th>
                <th rowspan="2" class="text-left" style="width: 95pt;">Nama</th>
                <th rowspan="2" class="text-left" style="width: 65pt;">Jabatan</th>
                @foreach($dates as $date)
                    <th class="{{ $date->isSunday() ? 'sunday-header' : '' }}" style="width: {{ $dateColWidth }}pt;">
                        {{ $date->format('j') }}
                    </th>
                @endforeach
            </tr>
            {{-- Row 2: Day Letters (R/K/J/S/M) --}}
            <tr>
                @foreach($dates as $date)
                    <th class="{{ $date->isSunday() ? 'sunday-header' : '' }}">
                        {{ getDayLetter($date) }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($karyawans as $index => $karyawan)
                <tr>
                    <td style="width: 14pt;">{{ $index + 1 }}</td>
                    <td class="text-left" style="width: 95pt;">
                        {{ $karyawan['nama'] }}
                    </td>
                    <td class="text-left" style="width: 65pt;">
                        {{ $karyawan['jabatan'] }}
                    </td>

                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dateObj = $dates[$d - 1];
                            $detail = $karyawan['details'][$d] ?? null;
                            $dateStr = $dateObj->format('Y-m-d');
                            $cutiNo = $cutiDates["{$karyawan['id']}-{$dateStr}"] ?? null;

                            if ($cutiNo) {
                                $cW = '#fee2e2';
                                $cT = '#b91c1c';
                                $cL = 'CUTI';
                            } elseif ($detail && $detail->shift) {
                                $cW = autoWarna($detail->shift->kode, $detail->shift->warna);
                                $cT = teksCerahGelap($cW);
                                $cL = labelSingkat($detail->shift->kode, $detail->shift->nama);
                            } else {
                                $cW = '#ffffff';
                                $cT = '#000000';
                                $cL = 'L';
                            }
                        @endphp

                        <td style="background-color: {{ $cW }}; color: {{ $cT }} !important; font-weight: {{ in_array($cL, ['L', 'CUTI']) ? 'bold' : 'bold' }};" class="{{ $dateObj->isSunday() ? 'sunday-cell' : '' }}">
                            {{ $cL }}
                        </td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + $daysInMonth }}" style="padding: 10px;">
                        Belum ada data pegawai untuk jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bottom Section (Keterangan & Signatures) --}}
    <div class="ket-section">
        <div class="ket-title">KETERANGAN</div>
        <div class="ket-text">
            1. Setiap Karyawan yang berhalangan hadir, wajib mencari pengganti sendiri (mengisi form yang telah di setujui atasan dan menyerahkannya ke SDM)<br>
            2. Setiap karyawan wajib hadir tepat waktu (untuk pelayanan wajib datang 15 menit sebelum shift dimulai)<br>
            3. Untuk pekerja Shift tidak boleh meninggalkan ruangan sebelum karyawan pengganti datang
        </div>

        {{-- 2 Columns: Left = Shift & Phone tables | Right = Signatures (Pushed down to align bottom) --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 2px;">
            <tr>
                {{-- Left Side: Shift Hours & Phone Table --}}
                <td style="width: 58%; vertical-align: top;">
                    {{-- Dynamic Shift Legend Table --}}
                    <table class="sub-table" style="width: 100%; margin-bottom: 4px;">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Kode</th>
                                <th style="width: 45%;">Nama Shift</th>
                                <th style="width: 25%;">Jam Kerja</th>
                                <th style="width: 15%;">Warna</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; font-weight: bold;">L / LIBUR</td>
                                <td>Hari Libur / Off</td>
                                <td style="text-align: center;">-</td>
                                <td style="background-color: #ffffff; text-align: center; font-weight: bold;">Putih</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #b91c1c;">CUTI</td>
                                <td>Cuti / Izin Resmi</td>
                                <td style="text-align: center;">-</td>
                                <td style="background-color: #fee2e2; color: #b91c1c; text-align: center; font-weight: bold;">Cuti</td>
                            </tr>
                            @foreach($shiftOptions as $so)
                                @php
                                    $sW = autoWarna($so['kode'], $so['warna']);
                                    $sT = teksCerahGelap($sW);
                                    $sL = labelSingkat($so['kode'], $so['nama']);
                                    $jamMasuk = !empty($so['jam_masuk']) ? substr($so['jam_masuk'], 0, 5) : '-';
                                    $jamKeluar = !empty($so['jam_keluar']) ? substr($so['jam_keluar'], 0, 5) : '-';
                                @endphp
                                <tr>
                                    <td style="text-align: center; font-weight: bold;">{{ $sL }}</td>
                                    <td>{{ $so['nama'] }}</td>
                                    <td style="text-align: center;">{{ $jamMasuk }} - {{ $jamKeluar }}</td>
                                    <td style="background-color: {{ $sW }}; color: {{ $sT }} !important; text-align: center; font-weight: bold;">
                                        {{ $sL }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Tabel No. Telepon Petugas --}}
                    <div style="font-size: 6.5pt; font-weight: bold; margin-top: 4px; margin-bottom: 2px; color: #000000 !important;">
                        4. No Tlp Petugas yang sedang Bertugas
                    </div>
                    @php
                        $halfCount = ceil(count($karyawans) / 2);
                        $leftKaryawans = array_slice($karyawans, 0, $halfCount);
                        $rightKaryawans = array_slice($karyawans, $halfCount);
                    @endphp
                    <table class="sub-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 32%;">Nama</th>
                                <th style="width: 18%;">No tip</th>
                                <th style="width: 32%;">Nama</th>
                                <th style="width: 18%;">No tip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < max(1, $halfCount); $i++)
                                @php
                                    $left = $leftKaryawans[$i] ?? null;
                                    $right = $rightKaryawans[$i] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $left['nama'] ?? '' }}</td>
                                    <td style="text-align: center;">{{ $left['hp'] ?? '' }}</td>
                                    <td>{{ $right['nama'] ?? '' }}</td>
                                    <td style="text-align: center;">{{ $right['hp'] ?? '' }}</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </td>

                {{-- Right Side: Signatures (Positioned at Bottom Right) --}}
                <td style="width: 42%; vertical-align: top; padding-left: 10pt;">
                    {{-- Top Spacer to push signatures down next to the bottom tables --}}
                    <div style="height: 65pt;"></div>

                    <table class="sig-container">
                        <tr>
                            <td class="sig-box">
                                <div>Koor {{ $jadwalKerja->isDokterSchedule() ? 'Dokter' : 'Karyawan' }} {{ $jadwalKerja->ruangan->nama ?? '' }}</div>
                                <div class="sig-space"></div>
                                <div class="sig-name">
                                    {{ $jadwalKerja->pembuat->full_nama ?? '..........................' }}
                                </div>
                            </td>
                            <td class="sig-box">
                                <div>Wadir Medis & Keperawatan</div>
                                <div class="sig-space"></div>
                                <div class="sig-name">
                                    {{ $jadwalKerja->disetujuiOleh->full_nama ?? $jadwalKerja->getTargetApproverName(2) }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
