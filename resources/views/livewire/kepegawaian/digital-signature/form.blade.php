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

                    editorWidth: 480,
                    updateEditorWidth() {
                        if (this.$refs.canvasBox) {
                            this.editorWidth = this.$refs.canvasBox.clientWidth || 480;
                        }
                    },

                    async renderPdfCanvas() {
                        if (typeof pdfjsLib === 'undefined') {
                            const script = document.createElement('script');
                            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                            script.onload = () => {
                                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                this.drawPage();
                            };
                            document.head.appendChild(script);
                        } else {
                            this.drawPage();
                        }
                    },

                    async drawPage() {
                        try {
                            const pdfUrl = '{{ $this->previewPdfUrl }}';
                            if (!pdfUrl) return;
                            const loadingTask = pdfjsLib.getDocument(pdfUrl);
                            const pdf = await loadingTask.promise;
                            const page = await pdf.getPage(1);
                            
                            const canvas = this.$refs.pdfCanvas;
                            const canvasBox = this.$refs.canvasBox;
                            if (!canvas || !canvasBox) return;
                            
                            this.updateEditorWidth();
                            const viewport = page.getViewport({ scale: 2.0 });
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            
                            // Dynamic aspect ratio matching natural PDF page dimensions
                            const pageAspectRatio = viewport.height / viewport.width;
                            canvasBox.style.aspectRatio = `1 / ${pageAspectRatio}`;

                            const context = canvas.getContext('2d');
                            await page.render({ canvasContext: context, viewport: viewport }).promise;
                        } catch (err) {
                            console.error('PDF.js render error in editor:', err);
                        }
                    },

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

                        this.posX = Math.max(0, Math.min(73, Math.round(pctX)));
                        this.posY = Math.max(0, Math.min(85, Math.round(pctY)));
                    },

                    stopDrag() {
                        this.isDragging = false;
                    }
                }"
                x-init="
                    $nextTick(() => renderPdfCanvas());
                    if (typeof Livewire !== 'undefined' && Livewire.hook) {
                        Livewire.hook('commit', ({ succeed }) => {
                            succeed(() => $nextTick(() => drawPage()));
                        });
                    }
                "
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
