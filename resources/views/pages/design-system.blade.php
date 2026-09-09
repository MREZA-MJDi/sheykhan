<x-layout.app>
    <main class="container-farzin py-8 sm:py-12">

        <div class="mb-8">
            <h1 class="text-2xl font-bold sm:text-3xl">
                Farzin Design System
            </h1>

            <p class="mt-2 text-sm text-[var(--color-text-muted)]">
                Component foundation
            </p>
        </div>

        <div class="grid gap-6">

            <x-ui.card>
                <h2 class="text-lg font-bold">Buttons</h2>

                <div class="mt-5 flex flex-wrap gap-3">
                    <x-ui.button>
                        Primary
                    </x-ui.button>

                    <x-ui.button variant="secondary">
                        Secondary
                    </x-ui.button>

                    <x-ui.button variant="outline">
                        Outline
                    </x-ui.button>

                    <x-ui.button variant="ghost">
                        Ghost
                    </x-ui.button>

                    <x-ui.button variant="danger">
                        Delete
                    </x-ui.button>

                    <x-ui.button variant="accent">
                        Accent
                    </x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-bold">Badges</h2>

                <div class="mt-5 flex flex-wrap gap-2">
                    <x-ui.badge>Neutral</x-ui.badge>
                    <x-ui.badge variant="primary">Primary</x-ui.badge>
                    <x-ui.badge variant="success">Success</x-ui.badge>
                    <x-ui.badge variant="warning">Warning</x-ui.badge>
                    <x-ui.badge variant="danger">Danger</x-ui.badge>
                    <x-ui.badge variant="info">Info</x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-bold">Form</h2>

                <div class="mt-5 max-w-xl">
                    <x-ui.input
                        id="email"
                        label="ایمیل"
                        type="email"
                        placeholder="example@email.com"
                    />
                </div>
            </x-ui.card>

        </div>

    </main>
</x-layout.app>