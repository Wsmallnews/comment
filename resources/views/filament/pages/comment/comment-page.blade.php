@php
    $scopeType = static::getScopeType();
    $scopeId = static::getScopeId();
    $contentType = static::getContentType();
    $properties = static::getProperties();
@endphp

<x-filament-panels::page>
    <livewire:sn-comment-fi-comments
        :properties="$properties"
        :scope-type="$scopeType" :scope-id="$scopeId"
        :content-type="$contentType"
        page-type="paginator"
        :load-children="false"
        :contained="true"
        :key="'fi-components-sn-comments:' . $record->id"
    />
</x-filament-panels::page>