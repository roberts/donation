<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            <x-filament::icon
                icon="heroicon-m-cpu-chip"
                class="h-6 w-6"
            />
        </div>

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                System Health
            </h2>

            <p class="fi-account-widget-user-name">
                Monitor queues, cache, and server performance
            </p>
        </div>

        <div class="fi-account-widget-logout-form">
            <x-filament::button
                color="gray"
                icon="heroicon-m-arrow-right"
                tag="a"
                href="/pulse"
                target="_blank"
            >
                Pulse
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
