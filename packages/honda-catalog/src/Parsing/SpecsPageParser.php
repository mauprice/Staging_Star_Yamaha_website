<?php

namespace Honda\Catalog\Parsing;

use Honda\Catalog\DataTransferObjects\SpecRow;
use Honda\Catalog\DataTransferObjects\SpecsPageData;
use Honda\Catalog\DataTransferObjects\VariantData;
use Symfony\Component\DomCrawler\Crawler;

class SpecsPageParser
{
    public function __construct(private readonly array $selectors = []) {}

    public function parse(string $html, string $sourceUrl): SpecsPageData
    {
        $crawler = new Crawler($html);
        $slug = $this->slugFromUrl($sourceUrl);

        $tableSelector = $this->selectors['table'] ?? 'table.specsTable__data';
        $table = $crawler->filter($tableSelector);

        if ($table->count() === 0) {
            return new SpecsPageData($slug, $sourceUrl, [], []);
        }

        $variantTitlesSelector = $this->selectors['variant_titles_row'] ?? '.spec--variantTitles';
        $headingSelector = $this->selectors['heading'] ?? '.specsTable__heading';
        $categorySelector = $this->selectors['category_name'] ?? '.specsTable__category-name';
        $subcategoryNameSelector = $this->selectors['subcategory_name'] ?? '.specsTable__subcategory-name';
        $subcategoryContentSelector = $this->selectors['subcategory_content'] ?? '.specsTable__subcategory-content';

        $variantColumns = [];
        $variantMap = []; // data-col => variant name
        $section = '';
        $category = '';
        $rows = [];
        $sort = 0;

        $table->first()->filter('tr')->each(function (Crawler $row) use (
            $variantTitlesSelector, $headingSelector, $categorySelector,
            $subcategoryNameSelector, $subcategoryContentSelector,
            &$variantColumns, &$variantMap, &$section, &$category, &$rows, &$sort
        ) {
            $variantCells = $row->filter($variantTitlesSelector);
            if ($variantCells->count() > 0) {
                $col = 0;
                $variantCells->each(function (Crawler $cell) use (&$col, &$variantColumns, &$variantMap) {
                    $name = trim($cell->text());
                    if ($name === '') {
                        // First (blank) label cell doesn't consume a data-col slot.
                        return;
                    }
                    $col++;
                    $variantMap[(string) $col] = $name;
                    $variantColumns[] = new VariantData(name: $name, priceCents: null, sort: $col - 1);
                });

                return;
            }

            $headingCell = $row->filter($headingSelector);
            if ($headingCell->count() > 0) {
                $section = trim($headingCell->first()->text());

                return;
            }

            $categoryCell = $row->filter($categorySelector);
            if ($categoryCell->count() > 0) {
                $category = trim($categoryCell->first()->text());

                return;
            }

            $labelCell = $row->filter($subcategoryNameSelector);
            if ($labelCell->count() === 0) {
                return;
            }

            $label = trim($labelCell->first()->text());
            if ($label === '') {
                return;
            }

            $row->filter($subcategoryContentSelector)->each(function (Crawler $cell) use (
                &$rows, &$sort, $section, $category, $label, $variantMap
            ) {
                $dataCol = $cell->attr('data-col');
                $value = trim($cell->text());

                $rows[] = new SpecRow(
                    section: $section,
                    category: $category,
                    label: $label,
                    value: $value !== '' ? $value : null,
                    variantName: $dataCol !== null ? ($variantMap[$dataCol] ?? null) : null,
                    sort: $sort++,
                );
            });
        });

        return new SpecsPageData($slug, $sourceUrl, $variantColumns, $rows);
    }

    private function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $segments = array_values(array_filter(explode('/', $path)));

        return $segments[3] ?? sha1($url);
    }
}
