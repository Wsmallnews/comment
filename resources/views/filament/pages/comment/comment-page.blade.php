@php
    $scopeType = static::getScopeType();
    $scopeId = static::getScopeId();
    $contentType = static::getContentType();
    $commentStatus = static::getCommentStatus();
    $properties = static::getProperties();
@endphp

<x-filament-panels::page>
    <livewire:sn-comment-fi-comments
        :properties="$properties"
        :scope-type="$scopeType" :scope-id="$scopeId"
        :content-type="$contentType" :comment-status="$commentStatus"
        page-type="paginator"
        :load-children="false"
        :contained="true"
        :key="'fi-components-sn-comments:' . $scopeType"
    />
</x-filament-panels::page>