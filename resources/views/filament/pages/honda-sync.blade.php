<x-filament-panels::page>

    {{-- Auto-refresh every 10 seconds so counts update after a background sync --}}
    <div wire:poll.10000ms>

        @php $lastSynced = $this->getLastSynced(); @endphp

        {{-- Last synced / never synced banner --}}
        <div style="border-radius:.75rem; border:1px solid {{ $lastSynced ? '#d1fae5' : '#fef3c7' }}; background:{{ $lastSynced ? '#f0fdf4' : '#fffbeb' }}; padding:1rem 1.25rem; display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
            <svg style="width:1.25rem;height:1.25rem;flex-shrink:0;color:{{ $lastSynced ? '#10b981' : '#f59e0b' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($lastSynced)
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                @endif
            </svg>
            <p style="font-size:.875rem; color:#374151; margin:0;">
                @if($lastSynced)
                    Last synced: <strong>{{ $lastSynced }}</strong>
                @else
                    No sync has been run yet. Click <strong>Sync Now</strong> to fetch model and offer data from the Honda Australia website.
                @endif
            </p>
        </div>

        {{-- Stats grid --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2rem;">
            @foreach($this->getStats() as $stat)
            <div style="border-radius:.75rem; border:1px solid #e5e7eb; background:#1f2937; padding:1.5rem; display:flex; flex-direction:column; align-items:center; text-align:center; gap:.75rem;">
                <p style="font-size:1.875rem; font-weight:900; color:#f9fafb; line-height:1; margin:0;">{{ $stat['value'] }}</p>
                <p style="font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.1em; margin:0;">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Info section --}}
        <div style="border-radius:.75rem; border:1px solid #374151; background:#1f2937; overflow:hidden;">
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #374151;">
                <h3 style="font-size:.8rem; font-weight:900; color:#f9fafb; text-transform:uppercase; letter-spacing:.08em; margin:0;">About the Honda Site Sync</h3>
            </div>
            <div style="padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.875rem;">
                @foreach([
                    'Crawls the Honda Australia sitemap and re-ingests model catalog data — descriptions, pricing, specs, colours and features.',
                    'Also crawls the Honda offers pages and syncs current promotions, including the ones shown in the homepage slider.',
                    'Unchanged models and offers are automatically detected and skipped, based on a content hash of the source page.',
                    'Runs in the background — you can navigate away and the counts above will update automatically once it finishes.',
                    'Run this whenever Honda updates model pricing, specs, or current offers.',
                ] as $point)
                <div style="display:flex; gap:.75rem; align-items:flex-start;">
                    <div style="width:.375rem; height:.375rem; border-radius:50%; background:#dc2626; flex-shrink:0; margin-top:.45rem;"></div>
                    <p style="font-size:.875rem; color:#9ca3af; margin:0; line-height:1.6;">{{ $point }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</x-filament-panels::page>
