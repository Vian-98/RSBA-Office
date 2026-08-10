<div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 space-y-6">
    {{-- Header & Stepper Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.header-stepper')

    <form wire:submit.prevent="openPasswordModal" class="space-y-6 w-full">
        {{-- Upload Dropzone Partial --}}
        @include('livewire.kepegawaian.digital-signature.partials.upload-dropzone')

        {{-- Side-by-Side Editor & PDF Preview Workstation --}}
        @if ($pdf_file && $this->previewPdfUrl)
            <div 
                x-data="{
                    posX: @entangle('stamp_x'),
                    posY: @entangle('stamp_y'),
                    scale: @entangle('stamp_scale'),
                    isDragging: false,
                    grabOffsetX: 0,
                    grabOffsetY: 0,

                    startDrag(e) {
                        this.isDragging = true;
                        const stampRect = $refs.stampBadge.getBoundingClientRect();
                        this.grabOffsetX = e.clientX - stampRect.left;
                        this.grabOffsetY = e.clientY - stampRect.top;
                    },

                    onDrag(e) {
                        if (!this.isDragging) return;
                        const canvasRect = $refs.canvasBox.getBoundingClientRect();
                        
                        let leftPx = e.clientX - canvasRect.left - this.grabOffsetX;
                        let topPx = e.clientY - canvasRect.top - this.grabOffsetY;
                        
                        let pctX = (leftPx / canvasRect.width) * 100;
                        let pctY = (topPx / canvasRect.height) * 100;

                        this.posX = Math.max(0, Math.min(75, Math.round(pctX)));
                        this.posY = Math.max(0, Math.min(85, Math.round(pctY)));
                    },

                    stopDrag() {
                        this.isDragging = false;
                    }
                }"
                @mousemove.window="onDrag($event)"
                @mouseup.window="stopDrag()"
                style="display: flex; flex-wrap: nowrap; gap: 24px; width: 100%; align-items: flex-start; border-top: 1px solid #f1f5f9; padding-top: 24px;"
            >
                {{-- Left Side: Metadata Form --}}
                @include('livewire.kepegawaian.digital-signature.partials.identity-form')

                {{-- Right Side: PDF Preview & Drag-and-Drop Stamp Canvas --}}
                @include('livewire.kepegawaian.digital-signature.partials.pdf-preview-canvas')
            </div>
        @endif
    </form>

    {{-- Security Password Confirmation Modal Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.password-modal')
</div>
