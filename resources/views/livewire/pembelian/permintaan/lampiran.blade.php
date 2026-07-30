<div x-data="corousel()">

    {{-- slide --}}
    <template x-for="(image,index) in slides" :key="index">
        <div class="slide" x-show="currentSlide === index">
            <img :src="'/storage/' + image" class="h-auto w-full object-cover transition-all duration-300 ease-in-out" x-transition>
        </div>
    </template>

    <!-- Navigation -->
    <div class="flex w-full items-center justify-between p-4">
        <span @click="prev" @keyup.left.window="prev" role="button">&#10094;</span>
        <span @click="next" @keyup.right.window="next" role="button">&#10095;</span>
    </div>
</div>

@script
    <script>
        Alpine.data('corousel', () => {
            return {
                currentSlide: 0,
                slides: @json($lampirans),

                next() {
                    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
                },

                prev() {
                    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
                }
            }
        });
    </script>
@endscript
