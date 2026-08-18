<x-filament-panels::page>

    {{-- Auto-refresh every 10 seconds so counts update after a background sync --}}
    <div wire:poll.10000ms>

        {{-- Progress / status banner --}}
        @php
            $lastSynced = $this->getLastSynced();
            $syncProgress = $this->getSyncProgress();
        @endphp

        @if($syncProgress['running'])
        {{-- Active progress bar --}}
        <div style="border-radius:.75rem; border:1px solid #1e3a5f; background:#0f172a; padding:1.25rem 1.5rem; margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem;">
                <div style="display:flex; align-items:center; gap:.625rem;">
                    <svg style="width:1rem;height:1rem;color:#dc2626;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span style="font-size:.875rem; font-weight:600; color:#f1f5f9;">{{ $syncProgress['label'] }}</span>
                </div>
                <span style="font-size:.875rem; font-weight:700; color:#dc2626;">{{ $syncProgress['pct'] }}%</span>
            </div>
            <div style="background:#1e293b; border-radius:9999px; height:.625rem; overflow:hidden;">
                <div style="height:100%; border-radius:9999px; background:linear-gradient(90deg,#dc2626,#ef4444); width:{{ $syncProgress['pct'] }}%; transition:width .4s ease;"></div>
            </div>
            @if($syncProgress['phase'] === 'products' && $syncProgress['total'] > 0)
            <p style="font-size:.75rem; color:#64748b; margin:.5rem 0 0;">
                {{ $syncProgress['current'] }} of {{ $syncProgress['total'] }} products — page updates every 10 seconds
            </p>
            @endif
        </div>
        <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
        @elseif($syncProgress['failed'] ?? false)
        {{-- Failed sync banner --}}
        <div style="border-radius:.75rem; border:1px solid #fecaca; background:#fef2f2; padding:1rem 1.25rem; display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
            <svg style="width:1.25rem;height:1.25rem;flex-shrink:0;color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <p style="font-size:.875rem; color:#991b1b; margin:0;">
                <strong>Sync failed:</strong> {{ $syncProgress['error'] ?? 'Unknown error.' }} Click <strong>Sync Now</strong> to try again.
            </p>
        </div>
        @else
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
                    No sync has been run yet. Click <strong>Sync Now</strong> to fetch product data from the Yamaha API.
                @endif
            </p>
        </div>
        @endif

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
                <h3 style="font-size:.8rem; font-weight:900; color:#f9fafb; text-transform:uppercase; letter-spacing:.08em; margin:0;">About the Yamaha API Sync</h3>
            </div>
            <div style="padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.875rem;">
                @foreach([
                    'Pulls all product data — descriptions, pricing, specs, colours, features, banners and gallery images — from the Yamaha Motor Australia API.',
                    'Also syncs current promotions displayed in the homepage slider and Specials & Offers section.',
                    'Takes approximately 2–3 minutes to complete. The sync runs in the background — you can navigate away and the counts above will update automatically.',
                    'Run this whenever Yamaha releases new models, updates pricing, or adds new promotions.',
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
