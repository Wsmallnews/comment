<x-filament-widgets::widget>
    <livewire:sn-category-fi-category :category-type="$record" :properties="$properties" :key="'components-' . $record->id . '-' . $record->level"/>
</x-filament-widgets::widget>
