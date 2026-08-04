<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($url as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
@isset($item['lastmod'])
        <lastmod>{{ $item['lastmod'] }}</lastmod>
@endisset
        <changefreq>{{ $item['changefreq'] }}</changefreq>
        <priority>{{ $item['priority'] }}</priority>
    </url>
@endforeach
</urlset>
