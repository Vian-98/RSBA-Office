@props([
    'id' => null,
])
@php
    $id = $id ?? 'month-picker-' . Str::random(8);
@endphp

<div 
    x-data="{
        open: false,
        value: @entangle($attributes->wire('model')),
        year: {{ date('Y') }},
        mode: 'months', // 'months' or 'years'
        yearRangeStart: {{ date('Y') - 4 }},
        months: [
            { val: '01', name: 'Januari', short: 'Jan' },
            { val: '02', name: 'Februari', short: 'Feb' },
            { val: '03', name: 'Maret', short: 'Mar' },
            { val: '04', name: 'April', short: 'Apr' },
            { val: '05', name: 'Mei', short: 'Mei' },
            { val: '06', name: 'Juni', short: 'Jun' },
            { val: '07', name: 'Juli', short: 'Jul' },
            { val: '08', name: 'Agustus', short: 'Ags' },
            { val: '09', name: 'September', short: 'Sep' },
            { val: '10', name: 'Oktober', short: 'Okt' },
            { val: '11', name: 'November', short: 'Nov' },
            { val: '12', name: 'Desember', short: 'Des' }
        ],
        init() {
            if (this.value) {
                const parts = this.value.split('-');
                if (parts.length === 2) {
                    this.year = parseInt(parts[0]);
                    this.yearRangeStart = this.year - 4;
                }
            } else {
                this.value = '{{ date('Y-m') }}';
            }
            
            // Watch for external value changes
            this.$watch('value', val => {
                if (val) {
                    const parts = val.split('-');
                    if (parts.length === 2) {
                        this.year = parseInt(parts[0]);
                        this.yearRangeStart = this.year - 4;
                    }
                }
            });
        },
        getLabel() {
            if (!this.value) return '';
            const parts = this.value.split('-');
            if (parts.length !== 2) return this.value;
            const y = parts[0];
            const mIdx = parseInt(parts[1]) - 1;
            if (mIdx >= 0 && mIdx < 12) {
                return this.months[mIdx].name + ' ' + y;
            }
            return this.value;
        },
        selectMonth(mVal) {
            const padMonth = mVal.toString().padStart(2, '0');
            this.value = this.year + '-' + padMonth;
            this.open = false;
        },
        selectYear(y) {
            this.year = y;
            this.mode = 'months';
        },
        getYearList() {
            let list = [];
            for (let i = 0; i < 12; i++) {
                list.push(this.yearRangeStart + i);
            }
            return list;
        },
        prev() {
            if (this.mode === 'months') {
                this.year--;
                this.yearRangeStart = this.year - 4;
            } else {
                this.yearRangeStart -= 12;
            }
        },
        next() {
            if (this.mode === 'months') {
                this.year++;
                this.yearRangeStart = this.year - 4;
            } else {
                this.yearRangeStart += 12;
            }
        }
    }"
    class="relative w-full"
    @click.away="open = false; mode = 'months';"
>
    <!-- Trigger Button -->
    <button 
        type="button" 
        @click="open = !open; mode = 'months';"
        class="flex w-full items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all duration-200"
    >
        <span x-text="getLabel()" class="truncate text-slate-700 font-medium"></span>
        <x-tabler-calendar class="h-4 w-4 flex-shrink-0 text-slate-400" />
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-1 w-64 rounded-xl border border-slate-100 bg-white p-3 shadow-lg"
        style="display: none;"
    >
        <!-- Selector Header -->
        <div class="flex items-center justify-between border-b border-slate-50 pb-2 mb-2">
            <button 
                type="button" 
                @click="prev()" 
                class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-colors"
            >
                <x-tabler-chevron-left class="h-4 w-4" />
            </button>
            
            <!-- Clickable Title Header -->
            <div class="flex items-center justify-center">
                <template x-if="mode === 'months'">
                    <button 
                        type="button"
                        @click="mode = 'years'; yearRangeStart = year - 4;"
                        class="font-bold text-slate-800 text-sm hover:text-indigo-600 hover:bg-slate-50 px-2 py-0.5 rounded transition-all duration-150"
                        title="Klik untuk pilih tahun"
                    >
                        <span x-text="year"></span>
                    </button>
                </template>
                <template x-if="mode === 'years'">
                    <button 
                        type="button"
                        @click="mode = 'months'"
                        class="font-bold text-slate-800 text-xs hover:text-indigo-600 hover:bg-slate-50 px-2 py-0.5 rounded transition-all duration-150"
                        title="Kembali ke bulan"
                    >
                        <span x-text="yearRangeStart + ' - ' + (yearRangeStart + 11)"></span>
                    </button>
                </template>
            </div>

            <button 
                type="button" 
                @click="next()" 
                class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-colors"
            >
                <x-tabler-chevron-right class="h-4 w-4" />
            </button>
        </div>

        <!-- Months Grid View -->
        <div x-show="mode === 'months'" class="grid grid-cols-3 gap-1.5">
            <template x-for="m in months" :key="m.val">
                <button
                    type="button"
                    @click="selectMonth(m.val)"
                    :class="{
                        'bg-indigo-600 text-white hover:bg-indigo-700 font-bold': value === (year + '-' + m.val),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-800 font-medium': value !== (year + '-' + m.val)
                    }"
                    class="rounded-lg py-2 text-xs text-center transition-all duration-200"
                    x-text="m.short"
                ></button>
            </template>
        </div>

        <!-- Years Grid View -->
        <div x-show="mode === 'years'" class="grid grid-cols-3 gap-1.5" style="display: none;">
            <template x-for="y in getYearList()" :key="y">
                <button
                    type="button"
                    @click="selectYear(y)"
                    :class="{
                        'bg-indigo-600 text-white hover:bg-indigo-700 font-bold': year === y,
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-800 font-medium': year !== y
                    }"
                    class="rounded-lg py-2 text-xs text-center transition-all duration-200"
                    x-text="y"
                ></button>
            </template>
        </div>
        
        <!-- Action Footer -->
        <div class="flex justify-between border-t border-slate-50 pt-2 mt-2">
            <button 
                type="button" 
                @click="value = '{{ date('Y-m') }}'; open = false; mode = 'months';"
                class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
            >
                Bulan Ini
            </button>
            <button 
                type="button" 
                @click="open = false; mode = 'months';"
                class="text-[10px] font-bold text-slate-400 hover:text-slate-600 transition-colors"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
