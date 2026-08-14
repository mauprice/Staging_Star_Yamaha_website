<?php

namespace Honda\Catalog\Parsing\Support;

final class PriceParser
{
    /**
     * Extracts a cents value from the first numeric token in a raw price
     * string, and returns whatever text remains (trimmed) as a label, e.g.
     * "Ride Away $12,999" -> [1299900, "Ride Away"].
     *
     * Never throws: missing/POA pricing is a normal, expected outcome (see
     * README - Honda's ride-away price is hydrated client-side and is
     * usually absent from the static HTML this parser sees), so an
     * unparsable string just yields a null price with the raw text kept as
     * the label.
     *
     * @return array{cents: ?int, label: ?string}
     */
    public static function toCents(?string $raw): array
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return ['cents' => null, 'label' => null];
        }

        if (! preg_match('/[$]?\s*[0-9][0-9,.]*/', $raw, $matches, PREG_OFFSET_CAPTURE)) {
            return ['cents' => null, 'label' => $raw];
        }

        [$token, $offset] = $matches[0];
        $label = trim(substr($raw, 0, $offset).substr($raw, $offset + strlen($token)));
        $numeric = str_replace(',', '', ltrim($token, '$ '));
        $dollars = (float) $numeric;

        return [
            'cents' => (int) round($dollars * 100),
            'label' => $label !== '' ? $label : null,
        ];
    }
}
