@php
    $user = auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            {{-- This is the custom part that forces the avatar to show --}}
            <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Avatar" class="h-10 w-10 rounded-full object-cover" />

            <div class="flex-1">
                <h2
                    class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white"
                >
                    {{ 'Welcome, ' . $user->name }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->email }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
