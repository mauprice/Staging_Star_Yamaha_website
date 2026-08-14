<?php

namespace App\Filament\Pages;

use App\Mail\CommissionReportMail;
use App\Models\StockEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SalesmanCommissionReport extends Page
{
    protected string $view = 'filament.admin.pages.salesman-commission-report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Commission Report';

    protected static ?string $title = 'Salesman Commission Report';

    protected static ?int $navigationSort = 2;

    public string $startDate = '';
    public string $endDate   = '';
    public string $salesmanFilter = '';
    public string $quickSelect    = 'week';
    public string $emailTo        = '';
    public ?string $pdfPath       = null;
    public ?string $pdfFilename   = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->endDate   = now()->toDateString();
    }

    protected function getViewData(): array
    {
        $rows  = $this->buildRows();
        $total = $rows->sum('commission');

        return [
            'salesmenOptions' => $this->buildSalesmenOptions(),
            'rows'            => $rows,
            'total'           => $total,
        ];
    }

    // ── Quick-select ───────────────────────────────────────────────────────

    public function setThisWeek(): void
    {
        $this->startDate   = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->endDate     = now()->toDateString();
        $this->quickSelect = 'week';
        $this->clearPdf();
    }

    public function updatedStartDate(): void      { $this->quickSelect = ''; $this->clearPdf(); }
    public function updatedEndDate(): void         { $this->quickSelect = ''; $this->clearPdf(); }
    public function updatedSalesmanFilter(): void  { $this->clearPdf(); }

    // ── PDF download ───────────────────────────────────────────────────────

    public function downloadPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows  = $this->buildRows();
        $total = $rows->sum('commission');
        $start = Carbon::parse($this->startDate);
        $end   = Carbon::parse($this->endDate);
        $salesman = $this->salesmanFilter ?: null;

        if ($rows->isEmpty()) {
            Notification::make()->warning()->title('No data')->body('No sold entries in the selected range.')->send();
            return response()->streamDownload(fn () => '', 'empty.pdf');
        }

        $content = Pdf::loadView('reports.salesman-commission', compact('rows', 'total', 'start', 'end', 'salesman'))
            ->setPaper('a4', 'portrait')
            ->output();

        $filename = 'commission-'.$start->format('Y-m-d').'-to-'.$end->format('Y-m-d').'.pdf';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    // ── Email ──────────────────────────────────────────────────────────────

    public function sendEmail(): void
    {
        $validator = Validator::make(
            ['email' => $this->emailTo],
            ['email' => ['required', 'email']],
            ['email.required' => 'Please enter an email address.', 'email.email' => 'Please enter a valid email address.']
        );

        if ($validator->fails()) {
            Notification::make()->danger()->title($validator->errors()->first('email'))->send();
            return;
        }

        $rows  = $this->buildRows();
        $total = $rows->sum('commission');
        $start = Carbon::parse($this->startDate);
        $end   = Carbon::parse($this->endDate);
        $salesman = $this->salesmanFilter ?: null;

        if ($rows->isEmpty()) {
            Notification::make()->warning()->title('No data')->body('No sold entries in the selected range.')->send();
            return;
        }

        $pdfContent = Pdf::loadView('reports.salesman-commission', compact('rows', 'total', 'start', 'end', 'salesman'))
            ->setPaper('a4', 'portrait')
            ->output();

        $filename = 'commission-'.$start->format('Y-m-d').'-to-'.$end->format('Y-m-d').'.pdf';

        Mail::to($this->emailTo)->send(new CommissionReportMail(
            pdfContent: $pdfContent,
            pdfFilename: $filename,
            start: $start,
            end: $end,
            salesman: $salesman,
            total: $total,
            count: $rows->count(),
        ));

        Notification::make()
            ->success()
            ->title('Report emailed')
            ->body('Sent to '.$this->emailTo)
            ->send();

        $this->emailTo = '';
    }

    // ── Data helpers ───────────────────────────────────────────────────────

    private function buildRows(): Collection
    {
        return StockEntry::sold()
            ->whereBetween('sold_date', [$this->startDate, $this->endDate])
            ->when($this->salesmanFilter !== '', fn ($q) => $q->where('salesman', $this->salesmanFilter))
            ->orderBy('sold_date')
            ->get()
            ->map(fn (StockEntry $e) => (object) [
                'date'       => $e->sold_date?->format('d/m/Y') ?? '',
                'type'       => $e->new_or_used ?? '',
                'model'      => $e->description ?? '',
                'customer'   => $e->sold_to ?? '',
                'salesman'   => $e->salesman ?? '',
                'commission' => $this->resolveCommission($e),
            ]);
    }

    private function resolveCommission(StockEntry $entry): float
    {
        if ($entry->commission !== null && (float) $entry->commission > 0) {
            return (float) $entry->commission;
        }
        return max(50.0, round((float) $entry->profit * 0.10, 2));
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

    private function clearPdf(): void
    {
        $this->pdfPath     = null;
        $this->pdfFilename = null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }
}
