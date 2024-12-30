<div class="container flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500" >

    <form class="w-full" wire:submit="create">
        {{ $this->form }}


        <x-jam-picture-f class="h-6 w-6 text-gray-400" />

        <x-filament::button type="submit">
            发送
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>