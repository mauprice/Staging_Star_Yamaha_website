<x-filament-panels::page>
<style>
.sd-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

@media (max-width: 900px) {
    .sd-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 500px) {
    .sd-stats { grid-template-columns: 1fr; }
}

.sd-stat {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    padding: 20px 24px;
}

.sd-stat-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 8px;
}

.sd-stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #f9fafb;
    line-height: 1;
}

.sd-stat-value.accent  { color: #f59e0b; }
.sd-stat-value.danger  { color: #ef4444; }
.sd-stat-value.success { color: #10b981; }

.sd-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.sd-card-header {
    padding: 16px 24px;
    border-bottom: 1px solid #374151;
    background: #111827;
    font-size: 14px;
    font-weight: 600;
    color: #f9fafb;
}

.sd-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.sd-table th {
    padding: 10px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.sd-table td {
    padding: 12px 16px;
    color: #d1d5db;
    border-bottom: 1px solid #1f2937;
    vertical-align: middle;
}

.sd-table tr:last-child td { border-bottom: none; }

.sd-table tr:hover td { background: #273349; }

.sd-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.sd-badge-unread   { background: #fef3c7; color: #92400e; }
.sd-badge-replied  { background: #d1fae5; color: #065f46; }
.sd-badge-pending  { background: #fee2e2; color: #991b1b; }

.sd-empty {
    padding: 40px;
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}

.sd-link {
    color: #f59e0b;
    text-decoration: none;
    font-weight: 500;
}

.sd-link:hover { text-decoration: underline; }
</style>

<div class="sd-stats">
    <div class="sd-stat">
        <div class="sd-stat-label">Total Bookings</div>
        <div class="sd-stat-value">{{ $totalCount }}</div>
    </div>
    <div class="sd-stat">
        <div class="sd-stat-label">Unread</div>
        <div class="sd-stat-value {{ $unreadCount > 0 ? 'accent' : 'success' }}">{{ $unreadCount }}</div>
    </div>
    <div class="sd-stat">
        <div class="sd-stat-label">Awaiting Reply</div>
        <div class="sd-stat-value {{ $unrepliedCount > 0 ? 'danger' : 'success' }}">{{ $unrepliedCount }}</div>
    </div>
    <div class="sd-stat">
        <div class="sd-stat-label">Today's Appointments</div>
        <div class="sd-stat-value {{ $todayCount > 0 ? 'accent' : '' }}">{{ $todayCount }}</div>
    </div>
</div>

<div class="sd-card">
    <div class="sd-card-header">Recent Service Bookings</div>

    @if($recentBookings->isEmpty())
        <div class="sd-empty">No service bookings yet.</div>
    @else
        <table class="sd-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Service</th>
                    <th>Preferred Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $booking)
                <tr>
                    <td>
                        <div style="font-weight:600; color:#f9fafb;">{{ $booking->name }}</div>
                        <div style="font-size:12px; color:#6b7280;">{{ $booking->phone }}</div>
                    </td>
                    <td>
                        {{ $booking->vehicle_year }} {{ $booking->vehicle_model }}
                        @if($booking->rego)
                            <div style="font-size:12px; color:#6b7280;">{{ $booking->rego }}</div>
                        @endif
                    </td>
                    <td>{{ $booking->service_type }}</td>
                    <td>{{ $booking->preferred_date->format('d/m/Y') }} <span style="color:#6b7280;">{{ $booking->preferred_time }}</span></td>
                    <td>
                        @if($booking->hasBeenReplied())
                            <span class="sd-badge sd-badge-replied">Replied</span>
                        @elseif($booking->isUnread())
                            <span class="sd-badge sd-badge-unread">Unread</span>
                        @else
                            <span class="sd-badge sd-badge-pending">Awaiting Reply</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</x-filament-panels::page>
