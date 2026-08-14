<?php

namespace App\Filament\Pages;

use App\Mail\SalesmanRegisterMail;
use App\Models\StockEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalesmanRegisterReport extends Page
{
    protected string $view = 'filament.admin.pages.salesman-register-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Salesman Register';

    protected static ?string $title = 'Salesman Register Report';

    protected static ?int $navigationSort = 1;

    public string $startDate = '';
    public string $endDate = '';
    public string $format = 'pdf';
    public string $salesmanFilter = '';
    public string $quickSelect = '';
    public ?string $reportPath = null;
    public ?string $reportFilename = null;
    public bool $showEmailForm = false;
    public string $emailTo = '';

    public function mount(): void
    {
        $lastSoldDate = StockEntry::sold()->latest('sold_date')->value('sold_date');
        $reference = $lastSoldDate ? Carbon::parse($lastSoldDate) : now();

        $this->startDate = $reference->copy()->startOfMonth()->toDateString();
        $this->endDate   = $reference->copy()->endOfMonth()->toDateString();
        $this->emailTo   = auth()->user()?->email ?? '';
    }

    protected function getViewData(): array
    {
        return [
            'salesmenOptions' => $this->buildSalesmenOptions(),
        ];
    }

    // ── Quick-select actions ────────────────────────────────────────────────

    public function setCurrentMonth(): void
    {
        $this->startDate  = now()->startOfMonth()->toDateString();
        $this->endDate    = now()->endOfMonth()->toDateString();
        $this->quickSelect = 'current';
        $this->clearReport();
    }

    public function setLastTwoMonths(): void
    {
        $this->startDate  = now()->subMonthWithoutOverflow()->startOfMonth()->toDateString();
        $this->endDate    = now()->endOfMonth()->toDateString();
        $this->quickSelect = 'last2';
        $this->clearReport();
    }

    public function updatedStartDate(): void      { $this->quickSelect = ''; $this->clearReport(); }
    public function updatedEndDate(): void         { $this->quickSelect = ''; $this->clearReport(); }
    public function updatedSalesmanFilter(): void  { $this->clearReport(); }

    // ── Generate ───────────────────────────────────────────────────────────

    public function generate(): void
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'format'    => 'required|in:pdf,csv',
        ]);

        $this->clearReport();

        $entries = $this->getEntries();

        if ($entries->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('No data found')
                ->body('No sold entries in the selected date range.')
                ->send();
            return;
        }

        Storage::disk('local')->makeDirectory('reports');

        $start = Carbon::parse($this->startDate);
        $end   = Carbon::parse($this->endDate);
        $uuid  = Str::uuid();

        if ($this->format === 'pdf') {
            [$groups, $totals] = $this->buildReportGroups($entries, $start);
            $content = Pdf::loadView('reports.salesman-register', compact('groups', 'totals', 'start', 'end'))
                ->setPaper('a4', 'landscape')
                ->output();
            $path = "reports/{$uuid}.pdf";
        } else {
            $content = $this->generateCsv($entries, $start);
            $path    = "reports/{$uuid}.csv";
        }

        Storage::disk('local')->put($path, $content);

        $this->reportPath     = $path;
        $this->reportFilename = 'salesman-register-'.$start->format('Y-m-d').'-to-'.$end->format('Y-m-d').'.'.$this->format;

        Notification::make()
            ->success()
            ->title('Report ready')
            ->body(strtoupper($this->format).' generated — '.count($entries).' entries.')
            ->send();
    }

    public function downloadReport(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_if(! $this->reportPath, 404);

        $content  = Storage::disk('local')->get($this->reportPath);
        $filename = $this->reportFilename;
        $mime     = $this->format === 'pdf' ? 'application/pdf' : 'text/csv';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => $mime]);
    }

    public function toggleEmailForm(): void
    {
        $this->showEmailForm = ! $this->showEmailForm;
    }

    public function sendEmail(): void
    {
        $this->validate(['emailTo' => 'required|email']);
        abort_if(! $this->reportPath, 404);

        Mail::to($this->emailTo)->send(new SalesmanRegisterMail(
            filePath: Storage::disk('local')->path($this->reportPath),
            filename: $this->reportFilename,
            format:   $this->format,
            startDate: Carbon::parse($this->startDate),
            endDate:   Carbon::parse($this->endDate),
        ));

        $this->showEmailForm = false;

        Notification::make()
            ->success()
            ->title('Report sent')
            ->body("Emailed to {$this->emailTo}")
            ->send();
    }

    // ── Data helpers ───────────────────────────────────────────────────────

    private function clearReport(): void
    {
        if ($this->reportPath && Storage::disk('local')->exists($this->reportPath)) {
            Storage::disk('local')->delete($this->reportPath);
        }
        $this->reportPath     = null;
        $this->reportFilename = null;
        $this->showEmailForm  = false;
    }

    private function getEntries()
    {
        return StockEntry::sold()
            ->whereBetween('sold_date', [$this->startDate, $this->endDate])
            ->when($this->salesmanFilter !== '', fn ($q) => $q->where('salesman', $this->salesmanFilter))
            ->orderByRaw('ISNULL(comm_paid_date), comm_paid_date ASC, sold_date ASC')
            ->get();
    }

    private function calculateCommission(StockEntry $entry): float
    {
        if ($entry->commission !== null) {
            return (float) $entry->commission;
        }

        return max(50.0, round((float) $entry->profit * 0.10, 2));
    }

    private function buildReportGroups($entries, Carbon $start): array
    {
        $yearlyOffset = StockEntry::sold()
            ->whereYear('sold_date', $start->year)
            ->where('sold_date', '<', $this->startDate)
            ->when($this->salesmanFilter !== '', fn ($q) => $q->where('salesman', $this->salesmanFilter))
            ->count();

        $groups      = [];
        $salesNo     = 0;
        $yearlyNo    = $yearlyOffset;
        $grandComm   = 0.0;
        $grandProfit = 0.0;

        foreach ($entries as $entry) {
            $key = $entry->comm_paid_date
                ? $entry->comm_paid_date->toDateString()
                : '__unpaid__';

            if (! isset($groups[$key])) {
                $groups[$key] = ['pay_date' => $entry->comm_paid_date, 'entries' => [], 'group_comm' => 0.0];
            }

            $salesNo++;
            $yearlyNo++;
            $comm = $this->calculateCommission($entry);

            $groups[$key]['entries'][] = [
                'entry'      => $entry,
                'commission' => $comm,
                'sales_no'   => $salesNo,
                'yearly_no'  => $yearlyNo,
            ];
            $groups[$key]['group_comm'] += $comm;
            $grandComm                 += $comm;
            $grandProfit               += (float) $entry->profit;
        }

        return [$groups, ['commission' => $grandComm, 'profit' => $grandProfit, 'sales' => $salesNo]];
    }

    private function generateCsv($entries, Carbon $start): string
    {
        [$groups, $totals] = $this->buildReportGroups($entries, $start);

        $rows   = [];
        $rows[] = ['DATE', 'NEW/USED', 'BIKE', 'CUSTOMER', 'COMMS', 'PAY DATE', 'PROFIT', 'SALES', 'YEARLY', 'YMF'];

        foreach ($groups as $group) {
            foreach ($group['entries'] as $item) {
                $entry  = $item['entry'];
                $rows[] = [
                    $entry->sold_date ? $entry->sold_date->format('d/m/Y') : '',
                    $entry->new_or_used ?? '',
                    $entry->description ?? '',
                    $entry->sold_to ?? '',
                    number_format($item['commission'], 2),
                    '',
                    number_format((float) $entry->profit, 2),
                    $item['sales_no'],
                    $item['yearly_no'],
                    $entry->ymaf_unit_no ?? '',
                ];
            }
            $rows[] = [
                '', '', '', '',
                number_format($group['group_comm'], 2),
                $group['pay_date'] ? $group['pay_date']->format('j-M') : '',
                '', '', '', '',
            ];
        }

        $rows[] = ['TOTAL', '', '', '', number_format($totals['commission'], 2), '', number_format($totals['profit'], 2), $totals['sales'], '', ''];

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function buildSalesmenOptions(): array
    {
        $salesmen = StockEntry::query()
            ->whereNotNull('salesman')
            ->distinct()
            ->orderBy('salesman')
            ->pluck('salesman', 'salesman')
            ->all();

        return ['' => 'All Salespeople'] + $salesmen;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }
}
