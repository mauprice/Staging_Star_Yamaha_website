<?php

namespace App\Console\Commands;

use App\Models\YamahaNews;
use App\Models\YamahaPromotion;
use App\Support\HtmlEntityDecoder;
use Illuminate\Console\Command;

class BackfillNewsEntities extends Command
{
    protected $signature = 'yamaha:backfill-entities
                            {--dry-run : Show counts without writing}';

    protected $description = 'Decode HTML entities in stored news and promotion text fields (idempotent)';

    public function handle(): int
    {
        $this->backfillNews();
        $this->backfillPromotions();

        $this->info('Backfill complete.');

        return self::SUCCESS;
    }

    private function backfillNews(): void
    {
        $updated = 0;

        YamahaNews::query()->chunkById(100, function ($rows) use (&$updated) {
            foreach ($rows as $row) {
                $head    = HtmlEntityDecoder::decode($row->head);
                $brief   = HtmlEntityDecoder::decode($row->brief);
                $content = HtmlEntityDecoder::decode($row->content);
                $type    = HtmlEntityDecoder::decode($row->type);

                if ($head === $row->head && $brief === $row->brief
                    && $content === $row->content && $type === $row->type) {
                    continue;
                }

                if (! $this->option('dry-run')) {
                    $row->updateQuietly(compact('head', 'brief', 'content', 'type'));
                }

                $updated++;
            }
        });

        $label = $this->option('dry-run') ? '(dry-run) would update' : 'Updated';
        $this->info("{$label} {$updated} news rows.");
    }

    private function backfillPromotions(): void
    {
        $updated = 0;

        YamahaPromotion::query()->chunkById(100, function ($rows) use (&$updated) {
            foreach ($rows as $row) {
                $head    = HtmlEntityDecoder::decode($row->head);
                $brief   = HtmlEntityDecoder::decode($row->brief);
                $content = HtmlEntityDecoder::decode($row->content);

                if ($head === $row->head && $brief === $row->brief && $content === $row->content) {
                    continue;
                }

                if (! $this->option('dry-run')) {
                    $row->updateQuietly(compact('head', 'brief', 'content'));
                }

                $updated++;
            }
        });

        $label = $this->option('dry-run') ? '(dry-run) would update' : 'Updated';
        $this->info("{$label} {$updated} promotion rows.");
    }
}
