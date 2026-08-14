<x-filament-panels::page>

    <style>
        .nc-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
        .nc-field { display:flex; flex-direction:column; gap:0.3rem; }
        .nc-label { font-size:0.72rem; font-weight:600; letter-spacing:0.04em; color:#6b7280; text-transform:uppercase; }
        .nc-date-wrap { position:relative; display:inline-flex; align-items:center; }
        .nc-date-wrap svg { position:absolute; left:0.5rem; width:1rem; height:1rem; color:#9ca3af; pointer-events:none; }
        .nc-field input[type="date"] {
            border:1px solid #d1d5db; border-radius:0.5rem;
            padding:0.42rem 0.65rem 0.42rem 2rem;
            font-size:0.875rem; color:#111827; background:#ffffff;
            min-width:160px; cursor:pointer;
        }
        .nc-field input[type="date"]:focus { outline:2px solid #f59e0b; outline-offset:1px; border-color:#f59e0b; }
        .nc-field select {
            border:1px solid #d1d5db; border-radius:0.5rem;
            padding:0.42rem 0.65rem; font-size:0.875rem;
            color:#111827; background:#ffffff; min-width:180px;
        }
        .nc-sep { width:1px; background:#e5e7eb; align-self:stretch; margin:0; }
        .nc-btn { display:inline-flex; align-items:center; gap:0.35rem; padding:0.42rem 0.9rem; border-radius:0.5rem; font-size:0.8rem; font-weight:600; cursor:pointer; border:1px solid #d1d5db; background:#f3f4f6; color:#374151; white-space:nowrap; }
        .nc-btn:hover { background:#e5e7eb; }
        .nc-btn.active { background:#f59e0b; border-color:#f59e0b; color:#fff; }
        .nc-btn.active:hover { background:#d97706; border-color:#d97706; }
        .nc-btn-pdf { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
        .nc-btn-pdf:hover { background:#1e40af; }
        .nc-btn-email { background:#059669; border-color:#059669; color:#fff; }
        .nc-btn-email:hover { background:#047857; }
        .nc-email-panel { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; }
        .nc-email-panel input[type="email"] {
            border:1px solid #d1d5db; border-radius:0.5rem;
            padding:0.42rem 0.65rem; font-size:0.875rem;
            color:#111827; background:#ffffff; width:220px;
        }
        .nc-email-panel input[type="email"]:focus { outline:2px solid #059669; outline-offset:1px; border-color:#059669; }

        /* Table */
        .nc-table-wrap { margin-top:0; overflow-x:auto; }
        .nc-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
        .nc-table thead th {
            background:#1d4ed8; color:#fff;
            padding:0.5rem 0.75rem; text-align:left;
            font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em;
            border:1px solid #1e40af;
        }
        .nc-table thead th.r { text-align:right; }
        .nc-table tbody td { padding:0.45rem 0.75rem; border:1px solid #d1d5db; color:#111827; background:#ffffff; }
        .nc-table tbody tr:nth-child(even) td { background:#f9fafb; }
        .nc-table tbody tr:hover td { background:#dbeafe; }
        .nc-table tfoot td {
            padding:0.5rem 0.75rem; border-top:2px solid #374151;
            background:#e5e7eb; color:#111827; font-weight:700; font-size:0.9rem;
        }
        .nc-comm { text-align:right; font-variant-numeric:tabular-nums; }
        .nc-empty { text-align:center; color:#9ca3af; padding:2rem; font-size:0.875rem; }
    </style>

    {{-- ── FILTERS ─────────────────────────────────────────────────────────── --}}
    <x-filament::section>
        <x-slot name="heading">Report Filters</x-slot>

        <div class="nc-row">

            <div class="nc-field">
                <span class="nc-label">Start Date</span>
                <div class="nc-date-wrap" x-data @click="$el.querySelector('input[type=date]').showPicker?.()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" wire:model.live="startDate" value="{{ $startDate }}" />
                </div>
            </div>

            <div class="nc-field">
                <span class="nc-label">End Date</span>
                <div class="nc-date-wrap" x-data @click="$el.querySelector('input[type=date]').showPicker?.()">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" wire:model.live="endDate" value="{{ $endDate }}" />
                </div>
            </div>

            <div class="nc-field">
                <span class="nc-label">Quick Select</span>
                <button
                    class="nc-btn {{ $quickSelect === 'week' ? 'active' : '' }}"
                    wire:click="setThisWeek"
                >This Week</button>
            </div>

            <div class="nc-sep"></div>

            <div class="nc-field">
                <span class="nc-label">Salesperson</span>
                <select wire:model.live="salesmanFilter">
                    @foreach($salesmenOptions as $val => $lbl)
                        <option value="{{ $val }}" @selected($salesmanFilter === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="nc-sep"></div>

            @if($rows->isNotEmpty())
                <div class="nc-field" style="justify-content:flex-end;">
                    <span class="nc-label">&nbsp;</span>
                    <button
                        class="nc-btn nc-btn-pdf"
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        wire:target="downloadPdf"
                    >
                        <span wire:loading.remove wire:target="downloadPdf">
                            <svg style="width:1rem;height:1rem;vertical-align:middle;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="downloadPdf">…</span>
                        <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                        <span wire:loading wire:target="downloadPdf">Generating…</span>
                    </button>
                </div>

                <div class="nc-field" x-data="{ open: false }">
                    <span class="nc-label">&nbsp;</span>
                    <div class="nc-email-panel">
                        <button
                            class="nc-btn nc-btn-email"
                            type="button"
                            @click="open = !open"
                        >
                            <svg style="width:1rem;height:1rem;vertical-align:middle;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email PDF
                        </button>
                        <template x-if="open">
                            <div class="nc-email-panel" style="margin-top:0;">
                                <input
                                    type="email"
                                    wire:model.live="emailTo"
                                    placeholder="recipient@example.com"
                                    @keydown.enter="$wire.sendEmail()"
                                />
                                <button
                                    class="nc-btn nc-btn-email"
                                    wire:click="sendEmail"
                                    wire:loading.attr="disabled"
                                    wire:target="sendEmail"
                                    type="button"
                                >
                                    <span wire:loading.remove wire:target="sendEmail">Send</span>
                                    <span wire:loading wire:target="sendEmail">Sending…</span>
                                </button>
                                <button
                                    class="nc-btn"
                                    type="button"
                                    @click="open = false; $wire.set('emailTo', '')"
                                    style="padding:0.42rem 0.6rem;"
                                >&#x2715;</button>
                            </div>
                        </template>
                    </div>
                </div>
            @endif

        </div>
    </x-filament::section>

    {{-- ── RESULTS TABLE ────────────────────────────────────────────────────── --}}
    <x-filament::section>
        <x-slot name="heading">
            Results
            @if($rows->isNotEmpty())
                <span style="font-size:0.8rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">
                    {{ $rows->count() }} {{ Str::plural('sale', $rows->count()) }}
                </span>
            @endif
        </x-slot>

        <div class="nc-table-wrap">
            @if($rows->isEmpty())
                <p class="nc-empty">No sold entries found for the selected period.</p>
            @else
                <table class="nc-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Model</th>
                            <th>Customer</th>
                            <th class="r">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td>{{ $row->type }}</td>
                                <td>{{ $row->model }}</td>
                                <td>{{ $row->customer }}</td>
                                <td class="nc-comm">$ {{ number_format($row->commission, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align:right;">TOTAL</td>
                            <td class="nc-comm">
                                <u><strong>$ {{ number_format($total, 2) }}</strong></u>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </x-filament::section>

</x-filament-panels::page>
