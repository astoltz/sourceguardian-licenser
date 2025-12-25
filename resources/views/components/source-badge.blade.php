@props(['source'])

@php
    $color = [
        'Project' => 'bg-primary',
        'Version' => 'bg-info',
        'Variation' => 'bg-success',
        'Customer' => 'bg-warning text-dark',
        'License' => 'bg-danger',
    ][$source] ?? 'bg-secondary';
@endphp

<span class="badge {{ $color }}">{{ $source }}</span>
