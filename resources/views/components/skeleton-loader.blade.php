@props([
    'loading' => 'loading',
    'type' => 'table', // table, cards, details
    'rows' => 5,
    'cols' => 5,
    'count' => 4
])

<div x-show="{{ $loading }}" x-cloak {{ $attributes }}>
    @if($type === 'table')
        <x-skeleton-table :rows="$rows" :cols="$cols" />
    @elseif($type === 'cards')
        <x-skeleton-cards :count="$count" :cols="$cols" />
    @elseif($type === 'details')
        <x-skeleton-details />
    @endif
</div>
