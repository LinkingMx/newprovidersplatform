@php
    $transitions = $getState();
    if (is_string($transitions)) {
        $transitions = json_decode($transitions, true);
    }

    if (!$transitions || empty($transitions)) {
        $transitions = [];
    }
@endphp

@if(empty($transitions))
    <span class="text-gray-400">—</span>
@else
    <div class="flex flex-wrap items-center gap-1.5">
        @foreach($transitions as $transition)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                {{ $transition }}
            </span>
            @if(!$loop->last)
                <span class="text-gray-400">→</span>
            @endif
        @endforeach
    </div>
@endif
