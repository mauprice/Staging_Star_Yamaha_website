<x-filament-panels::page>

    <style>
        .ns-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
        .ns-field { display:flex; flex-direction:column; gap:0.3rem; }
        .ns-label { font-size:0.72rem; font-weight:600; letter-spacing:0.04em; color:#6b7280; text-transform:uppercase; }
        .ns-date-wrap { position:relative;display:inline-flex;align-items:center; }
        .ns-date-wrap svg { position:absolute;left:0.5rem;width:1rem;height:1rem;color:#9ca3af;pointer-events:none; }
        .ns-field input[type="date"] {
            border:1px solid #d1d5db;
            border-radius:0.5rem;
            padding:0.42rem 0.65rem 0.42rem 2rem;
            font-size:0.875rem;
            color:#111827;
            background:#ffffff;
            min-width:160px;
            cursor:pointer;
        }
        .ns-field input[type="date"]:focus { outline:2px solid #f59e0b;outline-offset:1px;border-color:#f59e0b; }
        .ns-field select {
            border:1px solid #d1d5db;
            border-radius:0.5rem;
            padding:0.42rem 0.65rem;
            font-size:0.875rem;
            color:#111827;
            background:#ffffff;
            min-width:155px;
        }
        .ns-sep { width:1px; background:#e5e7eb; align-self:stretch; margin:0; }
        .ns-btn { display:inline-flex;align-items:center;gap:0.35rem;padding:0.42rem 0.9rem;border-radius:0.5rem;font-size:0.8rem;font-weight:600;cursor:pointer;border:none;white-space:nowrap; }
        .ns-ghost { background:#f3f4f6;color:#374151;border:1px solid #d1d5db; }
        .ns-ghost:hover { background:#e5e7eb; }
        .ns-ghost.active { background:#f59e0b;color:#fff;border-color:#f59e0b; }
        .ns-ghost.active:hover { background:#d97706;border-color:#d97706; }
        .ns-amber { background:#f59e0b;color:#fff; }
        .ns-amber:hover { background:#d97706; }
        .ns-green { background:#16a34a;color:#fff; }
        .ns-green:hover { background:#15803d; }
        .ns-blue  { background:#2563eb;color:#fff; }
        .ns-blue:hover  { background:#1d4ed8; }
        .ns-fmt-wrap { display:flex;gap:0.3rem; }
        .ns-fmt { padding:0.4rem 0.75rem;border-radius:0.4rem;font-size:0.8rem;font-weight:600;border:1px solid #d1d5db;background:#f9fafb;color:#6b7280;cursor:pointer; }
        .ns-fmt.on { background:#f59e0b;border-color:#f59e0b;color:#fff; }
        .ns-result { display:flex;align-items:center;gap:1rem;flex-wrap:wrap;background:#f0fdf4;border:1px solid #86efac;border-radius:0.75rem;padding:1rem 1.25rem; }
        .ns-result-label { flex:1;min-width:180px; }
        .ns-result-label strong { display:block;font-size:0.9rem;color:#14532d; }
        .ns-result-label span { font-size:0.78rem;color:#166534;word-break:break-all; }
        .ns-result-actions { display:flex;gap:0.5rem;flex-wrap:wrap; }
        .ns-email { background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem 1.25rem; }
        .ns-email h4 { margin:0 0 0.75rem;font-size:0.85rem;font-weight:700;color:#1e3a8a; }
        .ns-email-row { display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap; }
        .ns-email-row input[type="email"] { flex:1;min-width:220px;border:1px solid #bfdbfe;border-radius:0.5rem;padding:0.42rem 0.65rem;font-size:0.875rem;color:#1e3a8a;background:#fff; }
        @keyframes ns-spin { to { transform:rotate(360deg); } }
        .ns-spin { animation:ns-spin 1s linear infinite;display:inline-block; }
    </style>

    {{-- ── FILTER PANEL ────────────────────────────────────────────────────── --}}
    <x-filament::section>
        <x-slot name="heading">Report Parameters</x-slot>

        <div class="ns-row">

            <div class="ns-field">
                <span class="ns-label">Start Date</span>
                <div class="ns-date-wrap" x-data @click="$el.querySelector('input[type=date]').showPicker?.()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" wire:model.live="startDate" value="{{ $startDate }}" />
                </div>
            </div>

            <div class="ns-field">
                <span class="ns-label">End Date</span>
                <div class="ns-date-wrap" x-data @click="$el.querySelector('input[type=date]').showPicker?.()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" wire:model.live="endDate" value="{{ $endDate }}" />
                </div>
            </div>

            <div class="ns-field">
                <span class="ns-label">Quick Select</span>
                <div style="display:flex;gap:0.4rem;">
                    <button
                        class="ns-btn ns-ghost {{ $quickSelect === 'current' ? 'active' : '' }}"
                        wire:click="setCurrentMonth"
                    >Current Month</button>
                    <button
                        class="ns-btn ns-ghost {{ $quickSelect === 'last2' ? 'active' : '' }}"
                        wire:click="setLastTwoMonths"
                    >Last 2 Months</button>
                </div>
            </div>

            <div class="ns-sep"></div>

            <div class="ns-field">
                <span class="ns-label">Salesperson</span>
                <select wire:model.live="salesmanFilter">
                    @foreach($salesmenOptions as $val => $lbl)
                        <option value="{{ $val }}" @selected($salesmanFilter === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ns-sep"></div>

            <div class="ns-field">
                <span class="ns-label">Format</span>
                <div class="ns-fmt-wrap">
                    <button class="ns-fmt {{ $format === 'pdf' ? 'on' : '' }}" wire:click="$set('format','pdf')">PDF</button>
                    <button class="ns-fmt {{ $format === 'csv' ? 'on' : '' }}" wire:click="$set('format','csv')">CSV</button>
                </div>
            </div>

            <div class="ns-sep"></div>

            <button
                class="ns-btn ns-amber"
                wire:click="generate"
                wire:loading.attr="disabled"
                wire:target="generate"
            >
                <span wire:loading.remove wire:target="generate">
                    <svg style="width:1rem;height:1rem;vertical-align:middle;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span wire:loading wire:target="generate" class="ns-spin" style="width:1rem;height:1rem;">&#9696;</span>
                <span wire:loading.remove wire:target="generate">Generate Report</span>
                <span wire:loading wire:target="generate">Generating…</span>
            </button>

        </div>
    </x-filament::section>

    {{-- ── RESULT ───────────────────────────────────────────────────────────── --}}
    @if($reportPath)
        <div class="ns-result">
            <svg style="width:2rem;height:2rem;color:#16a34a;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ns-result-label">
                <strong>Report Ready</strong>
                <span>{{ $reportFilename }}</span>
            </div>
            <div class="ns-result-actions">
                <button class="ns-btn ns-green" wire:click="downloadReport" wire:loading.attr="disabled" wire:target="downloadReport">
                    <svg style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download {{ strtoupper($format) }}
                </button>
                <button class="ns-btn ns-blue" wire:click="toggleEmailForm">
                    <svg style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Send by Email
                </button>
            </div>
        </div>

        @if($showEmailForm)
            <div class="ns-email">
                <h4>Send Report by Email</h4>
                <div class="ns-email-row">
                    <div class="ns-field" style="flex:1;">
                        <span class="ns-label">Email Address</span>
                        <input type="email" wire:model.live="emailTo" placeholder="recipient@example.com" value="{{ $emailTo }}" />
                    </div>
                    <button class="ns-btn ns-blue" wire:click="sendEmail" wire:loading.attr="disabled" wire:target="sendEmail">
                        <span wire:loading.remove wire:target="sendEmail">Send</span>
                        <span wire:loading wire:target="sendEmail">Sending…</span>
                    </button>
                    <button class="ns-btn ns-ghost" wire:click="toggleEmailForm">Cancel</button>
                </div>
            </div>
        @endif
    @endif

</x-filament-panels::page>
