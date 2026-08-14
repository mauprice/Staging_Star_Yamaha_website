<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach($staticPages as $url)
    <url>
        <loc>{{ $url }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    @foreach($productUrls as $item)
    <url>
        <loc>{{ $item['url'] }}</loc>
        <lastmod>{{ $item['updated'] }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    @foreach($preOwned as $listing)
    <url>
        <loc>{{ route('yamaha.preowned.show', ['id' => $listing->id, 'slug' => str($listing->title)->slug()]) }}</loc>
        <lastmod>{{ $listing->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    @foreach($specials as $special)
    <url>
        <loc>{{ route('yamaha.specials.show', ['id' => $special->id, 'slug' => str($special->title)->slug()]) }}</loc>
        <lastmod>{{ $special->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

</urlset>
