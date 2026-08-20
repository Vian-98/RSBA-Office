<div class="w-full space-y-6">
    {{-- Floating Toast Notifications Partial --}}
    @include('livewire.kepegawaian.digital-signature.partials.notification-toast')

    {{-- Render Upload & Sign Studio Directly --}}
    <div class="w-full">
        <livewire:kepegawaian.digital-signature.form />
    </div>
</div>
